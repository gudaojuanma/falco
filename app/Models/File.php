<?php

namespace App\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Text;
use Phalcon\Http\Request\File as RequestFile;
use Intervention\Image\ImageManagerStatic as Image;

class File extends Model
{
    public $id;

    public $folder_id;

    public $hash;

    public $name;

    public $mime;
    
    public $size;

    public $path;

    const THUMBNAIL_SIZE = [150, 150];

    public function initialize()
    {
        $this->setSource('files');
    }

    public function prepare(RequestFile $file)
    {
        $now = date('Y/m/d His');
        list($date, $time) = explode(' ', $now);

        $dir = uploads_path($date);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }

        $realName = sprintf('%s%s.%s', $time, mt_rand(1000, 9999), $file->getExtension());
        $this->path = sprintf('%s/%s', $date, $realName);
        $name = $file->getName();
        $this->name = ($position = strrpos($name, '.')) === false ? $name : substr($name, 0, $position);
        $this->mime = $file->getRealType();
        $this->size = $file->getSize();

        return sprintf('%s/%s', $dir, $realName);
    }

    public function makeThumbnail($size)
    {
        if (strncmp($this->mime, 'image', 5) === 0) {
            $path = $this->thumbnailPath($size);

            list($width, $height) = $size;
            $resize = Image::make(uploads_path($this->path))->resize($width, $height, function($constraint) {
                $constraint->aspectRatio();
            });
            return $resize->save(uploads_path($path));
        }
    }

    public function thumbnailPath($size)
    {
        $index = strrpos($this->path, '.');
        list($width, $height) = $size;
        if ($index === false) {
            return sprintf('%s%d_%d', $this->path, $width, $height);
        } else {
            return sprintf('%s%d_%d.%s', substr($this->path, 0, $index), $width, $height, substr($this->path, $index + 1));
        }
    }

    public function thumbnailSrc($size) 
    {
        $url = resolve('url');
        return $url->get('/uploads/' . $this->thumbnailPath($size));
    }

    public function getSrc()
    {
        $url = resolve('url');
        return $url->get('/uploads/' . $this->path);
    }

    public function getExtension()
    {
        return ($position = strrpos($this->path, '.')) === false ? '' : substr($this->path, $position + 1);
    }

    public function format()
    {
        $record = [
            'id' => $this->id,
            'src' => $this->thumbnailSrc(self::THUMBNAIL_SIZE),
            'name' => $this->name,
            'mime' => $this->mime,
            'size' => $this->size
        ];

        return $record;
    }

    public function delete()
    {            
        if (($result = parent::delete())) {
            $path = uploads_path($this->path);
            file_exists($path) && unlink($path);

            $thumbnailPath = uploads_path($this->thumbnailPath(self::THUMBNAIL_SIZE));
            file_exists($thumbnailPath) && unlink($thumbnailPath);
        }

        return $result;
    }
}