<?php

namespace App\Http\Controllers;

use Phalcon\Mvc\Controller;
use App\Models\File;

class FileController extends Controller
{
    public function indexAction()
    {
        $this->view->disable();

        $ids = $this->request->get('id');
        if (is_array($ids) && isset($ids[0])) {
            $ids = array_filter($ids, function($id) {
                return is_numeric($id);
            });

            $files = File::find([
                'conditions' => 'id IN ({ids:array})',
                'bind' => [
                    'ids' => $ids
                ]
            ]);

            $records = [];
            foreach ($files as $file) {
                $records[] = $file->format();
            }
            return $this->response->setJsonContent($records);
        }


        $folder_id = $this->request->getQuery('folder_id', 'int', 0);
        $conditions = 'folder_id=:folder_id:';
        $bind = ['folder_id' => $folder_id];

        if ($mime = $this->request->getQuery('mime')) {
            $conditions .= ' AND mime=:mime:';
            $bind['mime'] = $mime;
        }

        $except = $this->request->getQuery('except');
        if (is_array($except)) {
            $conditions .= ' AND id NOT IN ({except:array})';
            $bind['except'] = $except;
        }

        $files = File::find([
            'conditions' => $conditions,
            'bind' => $bind,
            'order' => 'id DESC, mime ASC'
        ]);

        $records = [];
        foreach ($files as $file) {
            $records[] = $file->format();;
        }
        return $this->response->setJsonContent($records);
    }

    public function showAction(File $file)
    {
        return $this->response->setJsonContent($file->format());
    }

    public function storeAction()
    {
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();
            foreach ($files as $file) {
                if ($file->getKey() === 'file') {
                    $hash = hash_file('sha256', $file->getTempName());

                    if (($model = File::findFirstByHash($hash))) {
                        return $this->response->setJsonContent(array_merge($model->format(), ['exists' => true]));
                    }

                    $model = new File();
                    $model->hash = $hash;
                    $model->folder_id = $this->request->getPost('folder_id', 'int');

                    if (!($filepath = $model->prepare($file))) {
                        return $this->response->internalServerError('prepare file failed');
                    }

                    if ($file->moveTo($filepath)) {
                        
                        if ($model->save()) {
                            $model->makeThumbnail(File::THUMBNAIL_SIZE);
                            // 指派异步上传云端任务
                            // $this->queue->dispatch(\App\Jobs\SyncFileToCloud);
                            return $this->response->setJsonContent($model->format());
                        }

                        unlink(uploads_path($model->path));
                        return $this->response->unprocessable($model);
                    }
                    
                    return $this->response->internalServerError('move file failed');
                }
            }
        }

        $errors = [
            'file' => 'no file selected'
        ];
        return $this->response->unprocessable($errors);
    }

    public function updateAction(File $file)
    {
        $data = $this->request->getJsonRawBody(true);
        if (isset($data['name']) && strlen($data['name'])) {
            $file->name = $data['name'];
        }

        if ($file->save()) {
            return $this->response->setJsonContent($file);
        }

        return $this->response->unprocessable($file);
    }

    public function moveAction()
    {
        $moved = [];
        $data = $this->request->getJsonRawBody(true);

        if (isset($data['id']) && is_array($data['id'])) {
            $files = File::find([
                'conditions' => 'id IN ({id:array})',
                'bind' => [
                    'id' => $data['id']
                ]
            ]);

            $this->db->begin();
            foreach ($files as $file) {
                $file->folder_id = $data['folder_id'] ?? 0;
                if (!$file->save()) {
                    $this->db->rollback();
                    return $this->response->internalServerError('Update file#' . $file->id . ' failed');
                }

                $moved[] = $file->id;
            }

            $this->db->commit();
            return $this->response->setJsonContent($moved);
        }
    }

    public function destoryAction(File $file)
    {
        if ($file->delete()) {
            return $this->response->setJsonContent($file);
        }
        
        return $this->response->unprocessable($file);
    }

    public function batchDestroyAction()
    {
        $deleted = [];

        $ids = $this->request->get('id');
        if (is_array($ids) && isset($ids[0])) {
            $ids = array_filter($ids, function($id) {
                return is_numeric($id);
            });

            $files = File::find([
                'conditions' => 'id IN ({ids:array})',
                'bind' => [
                    'ids' => $ids
                ]
            ]);

            $this->db->begin();
            foreach ($files as $file) {
                if (!$file->delete()) {
                    $this->db->rollback();
                    $defaultMessage = sprintf('Delete #%d %s failed', $file->id, $file->name);
                    return $this->response->internalServerError($defaultMessage);
                }

                $deleted[] = $file->id;
            }

            $this->db->commit();
            return $this->response->setJsonContent($deleted);
        }
    }
}
