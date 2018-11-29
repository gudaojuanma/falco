<?php

namespace App\Http\Routes;

use App\Core\Mvc\Router\Group;

class Web extends Group
{
    public function initialize()
    {
        $this->addGet('/', 'Home::index');

        $this->addGet('/folder', 'Folder::index');
        $this->addPost('/folder', 'Folder::store');
        $this->addPut('/folder/{folder:\d+}', 'Folder::update');
        $this->addDelete('/folder/{folder:\d+}', 'Folder::destroy');
        $this->addDelete('/folders', 'Folder::batchDestroy');

        $this->addGet('/file', 'File::index');
        $this->addPost('/file', 'File::store');
        $this->addPut('/file/{file:\d+}', 'File::update');
        $this->addDelete('/file/{file:\d+}', 'File::destroy');
        $this->addDelete('/files', 'File::batchDestroy');
    }
}