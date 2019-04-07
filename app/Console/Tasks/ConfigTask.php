<?php

namespace App\Console\Tasks;

use Phalcon\Cli\Task;
use App\Providers\ConfigServiceProvider;

class ConfigTask extends Task
{
    /**
     * @description Create a cache file for faster configuration loading
     */
    public function cacheAction()
    {
        ConfigServiceProvider::cache();
        $this->output->info('Configuration cached successfully!');
    }

    /**
     * @description Remove the configuration cache file
     */
    public function clearAction()
    {
        ConfigServiceProvider::clear();
        $this->output->info('Configuration cache cleared!');
    }
}
