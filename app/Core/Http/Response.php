<?php

namespace App\Core\Http;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @param string $location
     * @return self
     */
    public function smartRedirect($location) 
    {
        $request = resolve('request');

        if ($request->isAjax()) {
            return $this->ajaxRedirect($location);
        }

        return $this->redirect($location);
    }

    /**
     * @param string $location
     * @return self
     */
    public function ajaxRedirect($location) 
    {
        $url = resolve('url');

        $this->setJsonContent([
            'status' => 302,
            'location' => $url->get($location)
        ]);

        return $this;
    }

    public function notFound($content = null)
    {
        if (! is_null($content)) {
            $this->setContent($content);
        }

        $this->setStatusCode(404);

        return $this;
    }

    /**
     * return http status code 400
     *
     * @param string|array $content
     * @return self
     */
    public function badRequest($content = null)
    {
        if (! is_null($content)) {
            $this->setJsonContent($content);
        }

        $this->setStatusCode(400);

        return $this;
    }

    public function unauthorized($content = null)
    {
        if (! is_null($content)) {
            $this->setJsonContent($content);
        }

        $this->setStatusCode(401);

        return $this;
    }

    /**
     * @param string|array $content
     * @return self
     */
    public function forbidden($content = null)
    {
        if (! is_null($content)) {
            $this->setJsonContent($content);
        }

        $this->setStatusCode(403);

        return $this;
    }

    /**
     * @param $content
     * @return self
     */
    public function unprocessable($content = null)
    {
        if (! is_null($content)) {
            if ($content instanceof Model || $content instanceof Validation) {
                $this->setJsonContent(self::flatMessages($content->getMessages()));
            } else {
                $this->setJsonContent($content);
            }
        }

        $this->setStatusCode(422);

        return $this;
    }

    /**
     * @param $content
     * @return self
     */
    public function internalServerError($content = null)
    {
        if (! is_null($content)) {
            $this->setJsonContent($content);
        }

        $this->setStatusCode(500);

        return $this;
    }

    /**
     * @param mixed $messages
     * @return array
     */
    public static function flatMessages($messages)
    {
        $result = [];
        foreach ($messages as $message) {
            $fields = $message->getField();
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    $result[$field] = $message->getMessage();
                }
            } else {
                $result[$fields] = $message->getMessage();
            }
        }
        return $result;
    }
    
}
