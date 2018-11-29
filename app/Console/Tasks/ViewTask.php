<?php

namespace App\Console\Tasks;

use Phalcon\Cli\Task;

class ViewTask extends Task
{
    /**
     * @description Clear all compiled view files
     */
    public function clearAction()
    {
        $command = sprintf('rm -Rf %s/storage/views/*', BASE_PATH);
        system($command);
    }
}