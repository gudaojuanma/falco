<?php

namespace App\Database\Migrations;

use App\Core\Db\Column;
use Phalcon\Db\Index;

class CreateAssetsTable20180619154619
{
    public function up()
    {
        $connection = resolve('db');
        $schema = config('database.mysql.dbname');

        $definition = [
            'columns' => [
                new Column('id', [
                    'type' => Column::TYPE_INTEGER,
                    'size' => 11,
                    'unsigned' => true,
                    'notNull' => true,
                    'autoIncrement' => true,
                    'first' => true
                ]),
                new Column('hash', [
                    'type' => Column::TYPE_CHAR,
                    'size' => 64,
                    'notNull' => true
                ]),
                new Column('path', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 255,
                    'notNull' => true
                ]),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id']),
                new Index('hash_INDEX', ['hash']),
            ]
        ];

        $connection->createTable('assets', $schema, $definition);
    }

    public function down()
    {
        $connection = resolve('db');
        $schema = config('database.mysql.dbname');
        $connection->dropTable('assets', $schema);
    }
}