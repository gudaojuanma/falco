<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Folder extends Model
{
    public $id;

    public $folder_id;

    public $name;

    public function initialize()
    {
        $this->setSource('folders');

        $this->hasMany('id', self::class, 'folder_id', [
            'alias' => 'children'
        ]);

        $this->hasMany('id', File::class, 'folder_id', [
            'alias' => 'files'
        ]);
    }
}