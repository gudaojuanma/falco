<?php

namespace App\Console\Tasks;

use Phalcon\Cli\Task;
use App\Models\Asset;

class AssetsTask extends Task
{
    public function combingAction()
    {
        $this->combing(public_path('assets'));
        Asset::cache();
    }

    protected function combing($top, $exclude = [])
    {
        if (! file_exists($top)) {
            return $this->output->error('%s not exists', $top);
        }

        if (! is_dir($top)) {
            return $this->output->error('%s is not a directory', $top);
        }

        if (! is_readable($top)) {
            return $this->output->error('%s is not readable', $top);
        }

        // 遍历数据表，找到文件已被删除的记录，删除之；找到内容变更的文件，更新它的hash
        foreach (Asset::find() as $asset) {
            $path = $top . '/' . $asset->path;

            if (file_exists($path)) {
                $hash = hash_file('sha256', $path);

                if (strcmp($hash, $asset->hash) == 0) {
                    continue;
                }

                if ($asset->update(['hash' => $hash])) {
                    $this->output->info('[mod] %s', $asset->path);
                }
            } else {
                $asset->delete() && $this->output->info('[rem] %s', $asset->path);
            }
        }

        // 遍历目录，找到不在数据表中的文件，添加之
        $this->add($top, null, $exclude);
    }

    protected function add($top, $dir = null, $exclude = [])
    {
        if (is_null($dir)) {
            $dir = $top;
        }

        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $dir . '/' . $file;
            $subPath = substr($path, strlen($top) + 1);
            if (in_array($subPath, $exclude)) {
                continue;
            }

            if (is_dir($path)) {
                $this->add($top, $path, $exclude);
                continue;
            }

            $hash = hash_file('sha256', $path);

            if (!Asset::findFirstByHash($hash)) {
                $asset = new Asset();
                $asset->hash = $hash;
                $asset->path = $subPath;
                if (false === $asset->save()) {
                    foreach ($asset->getMessages() as $message) {
                        $this->output->error('%s: %s', $asset->path, $message->getMessage());
                    }
                } else {
                    $this->output->info('[add] %s', $asset->path);
                }
                unset($asset);
            }
        }
    }
}