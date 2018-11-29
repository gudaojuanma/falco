<?php

namespace App\Database\Migrations;

use App\Core\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;

class CreateFoldersTable20181128085245
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
                new Column('folder_id', [
                    'type' => Column::TYPE_INTEGER,
                    'size' => 11,
                    'unsigned' => true,
                    'default' => 0
                ]),
                new Column('name', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 100,
                    'notNull' => true
                ]),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'])
            ]
        ];

        $connection->createTable('folders', $schema, $definition);
    }

    public function down()
    {
        $connection = resolve('db');
        $schema = config('database.mysql.dbname');
        $connection->dropTable('folders', $schema);
    }
}