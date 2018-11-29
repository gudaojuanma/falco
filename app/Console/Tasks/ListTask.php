<?php

namespace App\Console\Tasks;

use ReflectionClass;

use Phalcon\Text;
use Phalcon\Version;
use Phalcon\Cli\Task;
use App\Library\Cli\Output;

class ListTask extends Task
{
    /**
     * @description Lists commands
     */
    public function mainAction()
    {
        $taskDir = __DIR__;

        $singleCommands = [];
        $tasks = [];
        $commandWidth = 0;
        foreach(scandir($taskDir) as $fileName) {
            if (Text::endsWith($fileName, 'Task.php')) {
                $taskName = Text::uncamelize(substr($fileName, 0, -8), '-');
                $tasks[$taskName] = [];
                $className = sprintf('\App\Console\Tasks\%s', substr($fileName, 0, -4));
                $reflection = new ReflectionClass($className);
                foreach ($reflection->getMethods() as $method) {
                    $methodName = $method->getName();
                    if ($method->isPublic() && (Text::endsWith($methodName, 'Action'))) {
                        $actionName = Text::uncamelize(substr($methodName, 0, -6), '-');
                        if ($taskName == 'main' && $actionName == 'main') {
                            continue;
                        }

                        $description = $this->parseDocCommentDescription($method->getDocComment());

                        if ($actionName === 'main') {
                            $command = $this->output->green('  ' . $taskName);
                            $commandWidth = max(strlen($command), $commandWidth);
                            $singleCommands[] = [$command, $description];
                        } else {
                            $command = $this->output->green('    ' . $taskName . ':' . $actionName);
                            $commandWidth = max(strlen($command), $commandWidth);
                            $tasks[$taskName][] = [$command, $description];
                        }
                    }
                }
            }
        }

        $commandWidth += 2;

        $version = $this->output->green(Version::get());
        $this->output->writeln('Falco (Base On Phalcon Framework %s)', $version);
        $this->output->writeln();
        $this->output->comment('Usage:');
        $this->output->writeln('  php falco task:action [options]');
        $this->output->writeln();

        $this->output->comment('Available commands:');
        foreach ($singleCommands as $command) {
            $this->output->writeln("%-{$commandWidth}s%s", $command[0], $command[1]);
        }

        foreach ($tasks as $name => $commands) {
            if (! isset($commands[0])) {
                continue;
            }

            $this->output->writeln('  ' . $name);
            foreach ($commands as $command) {
                $this->output->writeln("%-{$commandWidth}s%s", $command[0], $command[1]);
            }
        }
    }

    private function parseDocCommentDescription($docComment)
    {
        $description = '';
        if ($docComment) {
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                if (preg_match("/^\s*\*\s+\@description\s+(.*)$/", $line, $matches)) {
                    $description = $matches[1];
                }
            }
        }
        return $description;
    }
}