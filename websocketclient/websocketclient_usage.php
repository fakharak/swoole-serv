<?php

namespace Websocketclient;

include_once __DIR__ . '/../includes/Autoload.php';


use Websocketclient\WebSocketClient;

$ip = '127.0.0.1';
if (isset($argv[1]) && in_array($argv[1], ['remote'])) { // Set Default IP
    $ip = '45.76.35.99';
}

$w = new WebSocketClient($ip, 9501);
if ($x = $w->connect()) {
//    var_dump($x);
    //reload-code
    if (isset($argv[1])) { // Code Reloading
        $cmd = strtolower($argv[1]);
        if (in_array($cmd, ['reload-code', 'shutdown'])) {

        }
        echo PHP_EOL."sending ".$cmd.PHP_EOL;
        $w->send($cmd, 'text', 1);
        exit;
    }

    for ($i=1;$i<4;$i++)
        $w->send('test'.$i, 'text', 0);
    $w->send('end', 'text', 1);
    while(true) {
        $data = $w->recv();
        if ($data) {
            var_export($data);
            sleep(1);
        } else {
            break;
        }
    }
} else {
    echo "Could not connect to server".PHP_EOL;
}



/*
 *
 * use Swoole\Coroutine\HTTP\Client;

Co\run(function()
{
    $client = new Co\http\Client("127.0.0.1", 9501);

    $ret = $client->upgrade("/");

    if($ret)
    {
        while(true)
        {
            $client->push("Hello World!");
            var_dump($client->recv());
            Co\System::sleep(5);
        }
    }
});

 */
