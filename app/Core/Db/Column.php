<?php

namespace App\Core\Db;

use Phalcon\Db\Column as BaseColumn;

class Column extends BaseColumn
{
    protected $comment;

    public function __construct(string $name, array $definition)
    {
        if (isset($definition['comment'])) {
            $this->comment = $definition['comment'];
        }

        parent::__construct($name, $definition);
    }

    public function getComment()
    {
        return $this->comment;
    }
}