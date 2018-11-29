<?php

namespace App\Console\Tasks;

use Swoole\Process;
use ReflectionClass;
use Phalcon\Cli\Task;
use App\Core\JobInterface;

class QueueTask extends Task
{
    const SHUTDOWN_JOB_BODY = 'SHUTDOWN';

    public function workAction() 
    {
        $this->clear();

        $pidfile = storage_path('run/beanstalk.' . getmypid());
        touch($pidfile);

        while (($job = $this->beanstalk->reserve()) !== false) {
            if (! file_exists($pidfile)) {
                // 发现需要重启消费进程，将取出的任务放回队列
                $job->release(); 

                // 跳出循环后当前进程会退出，supervisor会重新启动新的进程
                break;
            }

            $message = $job->getBody();

            // Break the while loop and exit the current process when received shutdown job
            if (is_string($message) && $message === self::SHUTDOWN_JOB_BODY) {
                unlink($pidfile);
                $this->logger->debug('Shutdown queue cosume process ' . getmypid());

                if ($this->isWorking()) {
                    $job->release();
                } else {
                    $job->delete();
                    $this->logger->debug('Shutdown all queue cosume process');
                }

                break;
            }

            $this->logger->debug(sprintf("Reserve job: %s", json_encode($message)));

            try {
                $process = new Process(function() use ($message) {
                    $this->execute($message);
                });
                
                $pid = $process->start();

                $process->setTimeout(60);

                if (($result = Process::wait())) {
                    $this->logger->debug(sprintf('Process exit success: %s', json_encode($result)));
                } else {
                    $this->logger->error(sprintf('Process exit failed: %d', $pid));
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                continue;
            } 
                
            $job->delete();
        }
    }

    protected function execute($message)
    {
        if (is_array($message) && isset($message['classname'])) {
            $parameters = isset($message['parameters']) ? $message['parameters'] : [];
            if (!is_array($parameters)) {
                $parameters = [$parameters];
            }

            $classname = $message['classname'];
            if (! class_exists($classname)) {
                $error = sprintf('Job [%s] class not exists', $classname);
                $this->logger->error($error);
                return false;
            }

            $reflection = new ReflectionClass($classname);
            if (! $reflection->implementsInterface(JobInterface::class)) {
                $error = sprintf('Job [%s] class does not implements \\App\\Library\\JobInterface', $classname);
                $this->logger->error($error);
                return false;
            }
            
            $job = $reflection->newInstance();
            return call_user_func_array([$job, 'handle'], $parameters);
        }
    }

    /**
     * 是否还有正在工作的队列消费进程
     * @return boolean
     */
    protected function isWorking()
    {
        $dir = storage_path('run');
        foreach(scandir($dir) as $file) {
            $matches = [];
            if (preg_match("/^beanstalk\.(\d+)$/", $file, $matches)) {
                if (Process::kill($matches[1], 0)) {
                    return true;
                } else {
                    unlink($dir . '/' .  $file);
                    $this->logger->debug('Delete the useless pid file: ' . $file);
                }
            }
        }

        return false;
    }

    public function clear()
    {
        $dir = storage_path('run');
        foreach(scandir($dir) as $file) {
            $matches = [];
            if (preg_match("/^beanstalk\.(\d+)$/", $file, $matches)) {
                if (! Process::kill($matches[1], 0)) {
                    unlink($dir . '/' . $file);
                    $this->logger->debug('Delete the useless pid file: ' . $file);
                }
            }
        }
    }

    public function shutdownAction()
    {
        $this->beanstalk->put(self::SHUTDOWN_JOB_BODY, [
            'priority' => 0
        ]);
    }

    public function statsAction()
    {
        $stats = $this->beanstalk->stats();
        $data = [
            ['key', 'value', 'key', 'value', 'key', 'value']
        ];

        $i = 1;
        $line = [];
        foreach ($stats as $key => $value) {
            if ($i > 1 && $i % 3 == 1) {
                $data[] = $line;
                $line = [];
            }
            $line[] = $key;
            $line[] = $value;
            $i++;
        }

        $diff = 6 - count($line);
        if ($diff > 0) {
            for($j = 0;$j < $diff;$j++) {
                $line[] = '';
            }
        }
        $data[] = $line;

        $this->output->table($data);
    }

    protected function pidfile()
    {
        $pid = getmypid();
        return sprintf('%s/storage/run/beanstalk.%d', BASE_PATH, $pid);
    }
}
