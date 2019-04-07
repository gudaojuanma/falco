<?php

namespace App\Console\Tasks;

use Phalcon\Text;
use Phalcon\Cli\Task;
use App\Console\Output;
use Doctrine\Common\Inflector\Inflector;

class MakeTask extends Task
{
    /**
     * 创建迁移类文件
     *
     * @description Create a new migration file
     * @example php console make:model --model=User
     * @param array $params [model]
     * @return boolean
     */
    public function migrationAction($params)
    {
        $mode = isset($params['mode']) ? $params['mode'] : '';
        $tableName = isset($params['table']) ? $params['table'] : '';

        if (empty($mode)) {
            $mode = 'create';
        }

        if (!in_array($mode, ['create', 'alter'])) {
            $this->output->error('Option --mode is invalid');
            return false;
        }

        if (empty($tableName)) {
            $this->output->error('Option --table is required');
            return false;
        }

        $stub = file_get_contents(stubs_path('migration/' . $mode . '.stub'));
        $fileName = sprintf('%s%sTable%s', ucfirst($mode), Text::camelize($tableName, '_'), date('YmdHis'));
        $find = ['{{ className }}', '{{ tableName }}'];
        $replace = [$fileName, $tableName];
        $code = str_replace($find, $replace, $stub);
        $path = migrations_path(sprintf('%s.php', $fileName));

        if (file_exists($path)) {
            $this->output->comment('Migration %s already exists', $path);
            return false;
        }

        if (strcmp($mode, 'create') === 0) {
            $migrations = scandir(migrations_path());
            $cmpLength = strlen($fileName) - 14;
            foreach ($migrations as $migration) {
                if (strncmp($fileName, $migration, $cmpLength) === 0) {
                    $this->output->comment('Migration %s already exists', substr($fileName, 0, -14));
                    return false;
                }
            }
        }

        $length = file_put_contents($path, $code);
        if ($length === strlen($code)) {
            return $this->output->info('Migration %s generate successfully', $fileName);
        }

        $this->output->error('Migration %s generate failed', $fileName);
    }

    /**
     * 创建控制器类文件
     *
     * @description Create a new model class
     * @example php console make:model --model=User
     * @param array $params [model]
     * @return boolean
     */
    public function modelAction($params)
    {
        $model = isset($params['model']) ? trim($params['model'], '/') : '';

        if (empty($model)) {
            $this->output->error('Option --model is required');
            return false;
        }

        if (strpos($model, '/') === false) {
            $className = $model;
            $namespace = 'App\\Models';
        } else {
            $segments = explode('/', $model);
            $className = array_pop($segments);
            $namespace = 'App\\Models\\' . implode('\\', $segments);
        }

        $stub = file_get_contents(stubs_path('model.stub'));
        $find = ['{{ namespace }}', '{{ className }}', '{{ tableName }}'];
        $replace = [$namespace, $className, Inflector::pluralize(Text::uncamelize($className))];
        $code = str_replace($find, $replace, $stub);

        $path = sprintf('%s/app/Models/%s.php', BASE_PATH, $model);

        // 如果目录不存在则创建
        $dir = dirname($path);
        if (! file_exists($dir)) {
            mkdir($dir, 0755);
        }

        if (file_exists($path)) {
            $this->output->comment('Model %s already exists', $path);
            return false;
        }

        $length = file_put_contents($path, $code);
        if ($length === strlen($code)) {
            return $this->output->info('Model %s generate successfully', $model);
        }

        $this->output->error('Model %s generate failed', $model);
    }

    /**
     * 创建控制器类文件
     *
     * @description Create a new controller class
     * @example php console make:controller --module=Backend --model=User
     * @param array $params [module, model, curd]
     * @return boolean
     */
    public function controllerAction($params)
    {
        $model = $params['model'] ?? null;
        $force = isset($params['force']) ?: false;

        if (empty($model)) {
            $this->output->error('Option --model is required', $model);
            return false;
        }

        $modelClass = trim($model, '\\');
        if (strpos($modelClass, '\\') === false) {
            $modelName = $modelClass;
            $modelClass = 'App\\Models\\' . $modelName;
        } else {
            $segments = explode('\\', $modelClass);
            $modelName = array_pop($segments);
        }

        $stub = file_get_contents(stubs_path('controller.stub'));
        $find = ['{{ modelClass }}', '{{ modelName }}', '{{ instanceName }}'];
        $replace = [$modelClass, $modelName, lcfirst($modelName)];
        $code = str_replace($find, $replace, $stub);

        $controllersDir = sprintf('%s/app/Http/Controllers', BASE_PATH);
        if (! file_exists($controllersDir)) {
            $this->output->comment(sprintf('Controllers directory %s not exists', $controllersDir));
            return false;
        }

        $path = sprintf('%s/%sController.php', $controllersDir, $modelName);

        if (file_exists($path) && !$force) {
            $this->output->error('Controller %s already exists', $path);
            return false;
        }

        $length = file_put_contents($path, $code);
        if ($length === strlen($code)) {
            $this->output->info('Controller %s generate successfully', $path);
            return true;
        }

        $this->output->error('Controller %s generate failed', $path);
    }

    /**
     * 创建中间件类文件
     *
     * @description Create a new middleware class
     * @example php console make:middleware --middleware=Auth
     * @param array $params [middleware]
     * @return boolean
     */
    public function middlewareAction($params)
    {
        $middleware = isset($params['middleware']) ? trim($params['middleware']) : null;

        if (empty($middleware)) {
            $this->output->error('option --middleware= is empty ', $middleware);
            return false;
        }

        $stub = file_get_contents(stubs_path('middleware.stub'));

        $code = str_replace('{className}', $middleware, $stub);

        $path = sprintf('%s/app/Http/Middleware/%s.php', BASE_PATH, $middleware);

        if (file_exists($path)) {
            $this->output->comment(sprintf('Middleware %s already exists', $path));
            return false;
        }

        $length = file_put_contents($path, $code);
        if ($length === strlen($code)) {
            return $this->output->info('Middleware %s generate successfully', $middleware);
        }

        $this->output->error('Middleware %s generate failed', $middleware);
    }

    /**
     * 构建任务类文件
     *
     * @description Create a new task class
     * @example php console make:task --task=Main
     * @param array $params [task]
     * @return boolean
     */
    public function taskAction($params)
    {
        $task = isset($params['task']) ? $params['task'] : null;
        if (empty($task)) {
            $this->output->error('Option --task is required');
            return false;
        }

        $stub = file_get_contents(stubs_path('task.stub'));
        $className = Text::camelize($task) . 'Task';
        $code = str_replace('{{ className }}', $className, $stub);
        $path = sprintf('%s/app/Console/Tasks/%sTask.php', BASE_PATH, $task);

        if (file_exists($path)) {
            $this->output->comment('Task %s already exists', $path);
            return false;
        }

        $length = file_put_contents($path, $code);
        if ($length === strlen($code)) {
            return $this->output->info('Task %s generate successfully', $task);
        }

        $this->output->error('Task %s generate failed', $task);
    }
}
