<?php

namespace App\Core\Cli;

use Phalcon\Text;
use Phalcon\Cli\Console as BaseConsole;

class Console extends BaseConsole
{
    public function handle($arguments = null) {
        if (is_array($arguments)) {
            $arguments = $this->parseArguments($arguments);
        }
        
        return parent::handle($arguments);
    }

    protected function parseArguments($arguments) {
        $parameters = [];
        $name = null;
        $options = [];

        array_shift($arguments);

        foreach ($arguments as $index => $argument) {
            if (preg_match("/^\-\-(\w+)(?:=([\S]+))?$/", $argument, $matches)) {
                if ($name) {
                    $options[$name] = true;
                }

                if (strcmp($matches[1], 'module') === 0 && isset($matches[2])) {
                    $parameters['module'] = $matches[2];
                    continue;
                }

                $options[$matches[1]] = isset($matches[2]) ? $matches[2] : true;
                continue;
            }

            if (preg_match("/^\-(\w+)$/", $argument, $matches)) {
                if ($name) {
                    $options[$name] = true;
                }

                $name = $matches[1];
                continue;
            }

            if ($index === 0) {
                if (strpos($argument, ':') === false) {
                    // 是必须的，phalcon没有转换为驼峰
                    $parameters['task'] = Text::camelize($argument, '-');
                    continue;
                }

                list($task, $action) = explode(':', $argument);
                $parameters['task'] = Text::camelize($task, '-');
                $parameters['action'] = Text::camelize($action, '-');
                continue;
            }

            if ($name) {
                $options[$name] = $argument;
                $name = null;
            }
        }

        if ($name) {
            $options[$name] = true;
        }

        $parameters['params'] = $options;

        return $parameters;
    }
}