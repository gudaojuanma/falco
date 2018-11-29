<?php

namespace App\Console\Tasks;

use Phalcon\Text;
use Phalcon\Cli\Task;
use App\Models\File;

class UploadsTask extends Task
{
    /**
     * @description 清除上传目录中的空文件夹及不在数据库的文件
     */
    public function clearAction()
    {
        $dir = uploads_path();
        $this->clearInvalidFiles($dir, ['.gitignore']);
        $this->clearEmptyDirectories($dir, ['.gitignore']);
    }

    /**
     * 清除一个目录下的空文件夹
     * 
     * @param string $dir
     * @param array $exclude
     * @param integer $level
     * @return null
     */
    private function clearEmptyDirectories($dir, $exclude = [], $level = 0)
    {
        $nodes = scandir($dir);

        if ($level == 0) {
            $exclude = array_merge(['..', '.'], $exclude);
        }

        if (count($nodes) == 2) {
            if ($level > 0) {
                rmdir($dir);
                $this->output->comment('Remove empty directory %s', str_replace(uploads_path(), '', $dir));

                $level--;
                $this->clearEmptyDirectories(dirname($dir), $exclude, $level);
            }
        } else {
            $level++;
            foreach ($nodes as $node) {
                if (in_array($node, $exclude)) {
                    continue;
                }

                $path = sprintf(Text::endsWith($dir, '/') ? '%s%s' : '%s/%s', $dir, $node);
                if (is_dir($path)) {
                    $this->clearEmptyDirectories($path, $exclude, $level);
                }
            }
        }
    }

    /**
     * 删除上传目录中的无效文件
     * 
     * @param string $dir
     * @param array $exclude
     * @param integer $level
     * @return null
     */
    private function clearInvalidFiles($dir, $exclude = [], $level = 0)
    {
        $nodes = scandir($dir);

        if ($level == 0) {
            $exclude = array_merge(['.', '..'], $exclude);
        }

        foreach ($nodes as $node) {
            if (in_array($node, $exclude)) {
                continue;
            }

            if (preg_match("/\d+_\d+\.\w+$/", $node, $matches)) {
                continue;
            }

            $path = sprintf(Text::endsWith($dir, '/') ? '%s%s' : '%s/%s', $dir, $node);
            if (is_file($path)) {
                $local = str_replace(uploads_path(''), '', $path);
                if (! File::findFirstByPath($local)) {
                    unlink($path) && $this->output->comment('Delete invalid file %s', $local);
                    $this->deleteThumbnail($path);
                }
            } else if (is_dir($path)) {
                $level++;
                $this->clearInvalidFiles($path, $exclude, $level);
            }            
        }
    }

    protected function deleteThumbnail($path) 
    {
        // 清理缩略图
        $index = strrpos($path, '.');
        list($width, $height) = File::THUMBNAIL_SIZE;
        if ($index === false) {
            $thumbnail = sprintf('%s%d_%d', $path, $width, $height);
        } else {
            $thumbnail = sprintf('%s%d_%d.%s', substr($path, 0, $index), $width, $height, substr($path, $index + 1));
        }

        if (file_exists($thumbnail))  {
            unlink($thumbnail) && $this->output->comment('Delete invalid thumbnail file %s', str_replace(uploads_path(), '', $thumbnail));
        }
    }
}
