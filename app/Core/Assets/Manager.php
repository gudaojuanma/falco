<?php

namespace App\Core\Assets;

use Phalcon\Text;
use App\Models\Asset;
use Phalcon\Assets\Manager as BaseManager;

class Manager extends BaseManager
{
    protected $map = [];

    public function setMap($map)
    {
        $this->map = $map;
    }

    public function pick(...$parameters)
    {
        foreach ($parameters as $path) {
            if (isset($this->map[$path])) {
                $path = $this->map[$path];
            }

            $extension = ($position = strrpos($path, '.')) === false ? '' : substr($path, $position + 1);

            if (Text::startsWith($path, '/vendor/')) {
                $extension == 'js' ? $this->addJs($path) : $this->addCss($path);
                continue;
            }

            if (($hash = Asset::getHashByPath($path))) {
                $path = $path . '?' . $hash;
            }

            $path = '/assets/' . $path;
            $extension == 'js' ? $this->addJs($path) : $this->addCss($path);
        }
    }
}
