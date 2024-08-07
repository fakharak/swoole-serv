<?php
use Swoole\Coroutine;
use Al\Swow\Context;
use DB\DBFacade;
use Swoole\Runtime;
use Swoole\Http\Request;

class WebSocketController
{
    protected $webSocketServer;
    protected $frame;
    protected $dbConnectionPools;
    protected $postgresDbKey;
    protected $mySqlDbKey;

  public function __construct($webSocketServer, $frame, $dbConnectionPools, $postgresDbKey = null, $mySqlDbKey = null) {
      global $swoole_pg_db_key;
      global $swoole_mysql_db_key;
      global $server;
      $server = $this->webSocketServer = $webSocketServer;
      $this->frame = $frame;
      $this->dbConnectionPools = $dbConnectionPools;
      $this->postgresDbKey = $postgresDbKey ?? $swoole_pg_db_key;
      $this->mySqlDbKey = $mySqlDbKey ?? $swoole_mysql_db_key;

  }

  public function handle() {
      if ($this->frame->data == 'reload-code') {
          echo "Reloading Code Changes (by Reloading All Workers)".PHP_EOL;
          $this->webSocketServer->reload();
      } else {
          $objDbPool = $this->dbConnectionPools[$this->postgresDbKey];

          $record_set = new Swoole\Coroutine\Channel(1);

          go(function() use ($record_set, $objDbPool) {
              $db_query = 'SELECT * FROM users;';
              $dbFacade = new DBFacade();
              $db_result = $dbFacade->query($db_query, $objDbPool);
              $record_set->push($db_result);
          });

          print_r($record_set->pop());
          echo "Received from frame->fd: {$this->frame->fd}, frame->data: {$this->frame->data}, 
          frame->opcode: {$this->frame->opcode}, frame->fin:{$this->frame->finish}, frame->flags:{$this->frame->flags}\n";

          return array('data'=>"You sent {$this->frame->data} to the server");
      }
  }
}
