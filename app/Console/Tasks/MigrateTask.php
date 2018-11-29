<?php

namespace App\Console\Tasks;

use ReflectionClass;
use App\Core\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Cli\Task;
use App\Console\Output;
use App\Models\Migration as MigrationModel;

class MigrateTask extends Task
{
    const MIGRATION_PATTERN = "#^(Create|Alter)[a-zA-Z0-9]+(\d{14})\.php$#";

    public function initialize()
    {
        $this->tableAction();
    }

    public function resetAction()
    {
        $this->rollbackAction();
        $this->mainAction();
    }

    /**
     * @description Show the status of each migration
     */
    public function statusAction()
    {
        $all = [];
        $ran = [];

        $this->output->info('Ran:');
        $migrations = MigrationModel::find();
        foreach ($migrations as $migration) {
            $ran[] = $migration->migration;
            $this->output->info('  %d:%s', $migration->batch, $migration->migration);
        }
        unset($migration);

        $dir = migrations_path();
        foreach(scandir($dir) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            if (preg_match(self::MIGRATION_PATTERN, $file, $matches)) {
                $all[] = substr($file, 0, -4);
            }
        }

        $run = array_diff($all, $ran);
        if (count($run)) {
            $batch = MigrationModel::nextBatch();
            $this->output->comment('New:');
            foreach ($run as $migration) {
                $this->output->comment('%d:%s', $batch, $migration);
            }
        }
    }

    /**
     * @description Rollback the last database migration
     */
    public function rollbackAction()
    {
        $lastRanMigrations = MigrationModel::lastRanMigrations();

        if (count($lastRanMigrations) === 0) {
            $this->output->comment('No migrations to rollback');
            return false;
        }

        foreach ($lastRanMigrations as $lastRanMigration) {
            $index = $lastRanMigration->migration;

            $path = migrations_path(sprintf('%s.php', $index));
            if (!file_exists($path)) {
                $this->output->error('File %s not exists', $path);
                break;
            }

            $className = $this->migrationClassName($index);
            if (!class_exists($className)) {
                $this->output->error('Class %s not exists', $className);
                break;
            }

            $reflection = new ReflectionClass($className);
            $migration = $reflection->newInstance();

            try {
                call_user_func([$migration, 'down']);
            } catch (Exception $e) {
                $this->output->error($e->getMessage());
                break;
            }

            $lastRanMigration->delete();
            $this->output->info('Rollback %s successfully', $index);
        }
    }

    /**
     * @description Run the database migrations
     */
    public function mainAction()
    {
        $all = [];

        $dir = migrations_path();
        foreach(scandir($dir) as $file) {
            if (preg_match(self::MIGRATION_PATTERN, $file, $matches)) {
                $all[] = substr($file, 0, -4);
            }
        }

        $ran = [];
        $ranMigrations = MigrationModel::find();
        foreach ($ranMigrations as $ranMigration) {
            $ran[] = $ranMigration->migration;
        }

        $batch = MigrationModel::nextBatch();

        $run = array_diff($all, $ran);
        usort($run, function($a, $b) {
            return substr($a, -14) > substr($b, -14) ? 1 : -1;
        });

        if (count($run) === 0) {
            $this->output->comment('No migrations to run');
            return false;
        }

        foreach ($run as $index) {
            $className = $this->migrationClassName($index);

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $migration = $reflection->newInstance();

                try {
                    call_user_func([$migration, 'up']);
                    $model = new MigrationModel([
                        'migration' => $index,
                        'batch' => $batch
                    ]);
                    $model->create();
                } catch (Exception $e) {
                    $this->logger->log($e->getMessage());
                    break;
                }

                $this->output->info('Run Migration %s successfully', $index);
                continue;
            }

            $this->output->error('Class %s not exists', $className);
            break;
        }
    }

    private function tableAction()
    {
        $schema = config('database.mysql.dbname');
        $connection = resolve('db');
        $table = (new MigrationModel())->getSource();

        if ($connection->tableExists($table, $schema)) {
            //$this->output->comment('Table %s already exists', $table);
            return true;
        }

        $connection->createTable($table, $schema, [
            'columns' => [
                new Column('migration', [
                    'type' => Column::TYPE_VARCHAR,
                    'size' => 100,
                    'notNull' => true
                ]),

                new Column('batch', [
                    'type' => Column::TYPE_INTEGER,
                    'size' => 11,
                    'notNull' => true
                ])
            ],

            'indexes' => [
                new Index('PRIMARY', ['migration']),
                new Index('batch_INDEX', ['batch'])
            ]
        ]);

        $this->output->info('Table %s create successfully', $table);
    }

    private function migrationClassName($index)
    {
        return sprintf('\\App\\Database\\Migrations\\%s', $index);
    }
}