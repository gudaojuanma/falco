<?php

namespace App\Console\Tasks;

use Phalcon\Cli\Task;

class CacheTask extends Task
{
    /**
     * @description Flush the application cache
     */
    public function clearAction()
    {
        $command = sprintf('rm -Rf %s/storage/caches/*', BASE_PATH);
        system($command);
        $this->output->info('Clear caches successful');
    }
}