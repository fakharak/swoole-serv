<?php

namespace App\Services;

use Bootstrap\SwooleTableFactory;

class SwooleTableTestService
{
    protected $webSocketServer;
    protected $frame;

    protected $swooleTableFactory;

    public function __construct($webSocketServer, $frame)
    {
        $this->webSocketServer = $webSocketServer;
        $this->frame = $frame;

        // Create the Swoole Table
        // Types can be (string, int, float)
        // Size is required in case type is string or int
        $columns = [
            ['name' => 'email', 'type' => 'string', 'size' => 100],
            ['name' => 'rollno', 'type' => 'int', 'size' => 10],
            ['name' => 'height', 'type' => 'float'],
        ];

        // Create table (TableName, TotalRows, ColumnDefinitions)
        if (!SwooleTableFactory::tableExists('test_table')) {
            // dump('creating table');
            $this->swooleTableFactory = SwooleTableFactory::createTable('test_table', 32, $columns);
        }
    }

    public function handle()
    {
        // Following statement works but for elaboration purpose i am using getTable static method
        // $table = $this->swooleTableFactory;

        $table = SwooleTableFactory::getTable('test_table');
        if ($table === false) {
            echo 'table not found' . PHP_EOL;
            return;
        }

        // You can set the data in the table using the following code Example 1 and Example 2
        // in set() first parameter is the key of the data. It could be a string or integer. We can fetch the data using this key.
        // the second parameter is an array of values we want to store (According to defined Table Schema/Column Definition)
        // Example 1
        $table->set(0, ['email' => 'mohsin@gmail.com', 'rollno' => 16, 'height' => 5.8]);
        $table->set(1, ['email' => 'ali@gmail.com', 'rollno' => 12, 'height' => 5.3]);

        // Example 2: The following code I am using a string key.
        $table->set('student_one', ['email' => 'ali@gmail.com', 'rollno' => 12, 'height' => 5.3]);

        // We can delete the table Data using del() passing the key as parameter
        // You can use the $table->count() code to check number of rows in table
        $table->del(0);
        $table->del(1);
        $table->del('student_one');


        // In Swoole Table we have a limit on number of rows
        // So in-case we have more data rows, we can use the Update Table Size function to set the new size/length
        // In following example we have a table with 32 rows, then we will increase the size to 100
        // $table = SwooleTableFactory::updateTableSize($table, 1024);

        // Now we can store more than 32 rows into the table
        $size = 100;
        for ($i = 0; $i < $size; $i++) {
            $key = $i;
            $table = SwooleTableFactory::addData($table, $key, ['email' => 'mohsin.' . $i . '@gmail.com', 'rollno' => $i + 1, 'height' => 5.8]);
            // $table->set($i, ['email' => 'mohsin.' . $i . '@gmail.com', 'rollno' => 16 + $i, 'height' => 5.8]);
        }

        // We can check the size of the table using $table->getMaxSize()
        echo $table->count() . PHP_EOL;
        echo $table->getMaxSize() . PHP_EOL;

        go(function () use ($table, $size) {
            // We can get the record using get() passing the key of record/row
            // Following code will return us all the columns of row 0
            var_dump($table->get(0));

            // To get specific column/field we can pass that column name as second parameter in get()
            var_dump($table->get(1, 'rollno'));

            // To get data of associated key row. e.g below
            // var_dump($table->get('key_one', 'email'));

            echo '---------------------------' . PHP_EOL;
            echo 'For dynamic size update, we verify if the table has store all of our data' . PHP_EOL;
            echo '---------------------------' . PHP_EOL;
            for ($i = 0; $i < $size; $i++) {
                echo $table->get($i, 'rollno') . PHP_EOL;
            }
        });
    }
}
