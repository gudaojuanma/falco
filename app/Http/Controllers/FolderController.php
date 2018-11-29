<?php

namespace App\Http\Controllers;

use Phalcon\Mvc\Controller;
use App\Models\Folder;

class FolderController extends Controller {
    public function indexAction()
    {
        $folder_id = $this->request->getQuery('folder_id', 'int', 0);

        $page = $this->request->getQuery('page', 'int', 1);
        if ($page < 1) {
            $page = 1;
        }

        $page_size = $this->request->getQuery('page_size', 'int', 20);
        if ($page_size < 0) {
            $page_size = 20;
        }


        $folders = Folder::find([
            'conditions' => 'folder_id=:folder_id:',
            'bind' => [
                'folder_id' => $folder_id
            ],
            'limit' => $page_size,
            'offset' => ($page - 1) * $page_size
        ]);

        return $this->response->setJsonContent($folders);
    }

    public function storeAction()
    {
        $data = $this->request->getJsonRawBody(true);
        $folder = new Folder();
        $folder->folder_id = $data['folder_id'] ?? 0;
        $folder->name = $data['name'] ?? 'New Folder';
        if ($folder->save()) {
            return $this->response->setJsonContent($folder);
        }

        return $this->response->unprocessable($folder);
    }

    public function updateAction(Folder $folder)
    {
        $data = $this->request->getJsonRawBody(true);
        if (isset($data['name']) && strlen($data['name'])) {
            $folder->name = $data['name'];
            if (false === $folder->save()) {
                return $this->response->unprocessable($folder);
            }
        }

        return $this->response->setJsonContent($folder);
    }

    public function destoryAction(Folder $folder)
    {
        if ($folder->countFiles() > 0) {
            return $this->response->internalServerError('The folder has some files');
        }

        if ($folder->countChildren() > 0) {
            return $this->response->internalServerError('The folder has some folders');
        }

        if ($folder->delete()) {
            return $this->response->setJsonContent($folder);
        }
        
        return $this->response->unprocessable($folder);
    }
}