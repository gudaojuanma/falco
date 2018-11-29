<?php

namespace App\Database\Migrations;

use App\Core\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;

class CreateFilesTable20181004045900
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
                new Column('hash', [
                    'type' => Column::TYPE_CHAR,
                    'size' => 64,
                    'notNull' => true
                ]),
                new Column('name', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 100,
                    'notNull' => true
                ]),
                new Column('mime', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 45,
                    'notNull' => true
                ]),
                new Column('size', [
                    'type' => Column::TYPE_INTEGER,
                    'size' => 11,
                    'unsigned' => true,
                    'notNull' => true
                ]),
                new Column('path', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 255,
                    'notNull' => true
                ])
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'])
            ]
        ];

        $connection->createTable('files', $schema, $definition);
    }

    public function down()
    {
        $connection = resolve('db');
        $schema = config('database.mysql.dbname');
        $connection->dropTable('files', $schema);
    }
}