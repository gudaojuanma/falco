<?php

namespace App\Console\Tasks;

use Phalcon\Text;
use Phalcon\Cli\Task;
use Phalcon\Security\Random;

class KeyTask extends Task
{
    /**
     * @description Set the application key
     */
    public function generateAction()
    {
        $envFile = BASE_PATH . '/.env';
        $swapFile = BASE_PATH . '/.env.swap';
        
        $env = fopen($envFile, 'r');
        $swap = fopen($swapFile, 'w');

        while(($line = fgets($env))) {
            if (Text::startsWith($line, 'APP_KEY=')) {
                $random = new Random();
                $key = $random->hex(16);
                $line = sprintf("APP_KEY=%s\n", $key);
            }
            fwrite($swap, $line);
        }

        fclose($env);
        fclose($swap);

        rename(BASE_PATH . '/.env.swap', BASE_PATH . '/.env');

        if(isset($key)) {
            $this->output->info('Key generate successful %s', $key);
        }
    }

}
