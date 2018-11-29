<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Migration extends Model
{
    public $migration;

    public $batch;

    public function initialize()
    {
        $this->setSource('migrations');
    }

    public function datetime()
    {
        return substr($this->migration, -14);
    }

    public function laterThan(Migration $other)
    {
        return $this->datetime() > $other->datetime();
    }

    public static function lastBatch()
    {
        return self::maximum(['column' => 'batch']) ?: 0;
    }

    public static function nextBatch()
    {
        return self::lastBatch() + 1;
    }

    /**
     *
     * @return array
     */
    public static function lastRanMigrations()
    {
        $migrations = [];

        $batch = self::lastBatch();

        $lastRanMigrations = self::findByBatch($batch);

        if ($lastRanMigrations->count()) {
            foreach ($lastRanMigrations as $lastRanMigration) {
                $migrations[] = $lastRanMigration;
            }

            usort($migrations, function($a, $b) {
                return $a->laterThan($b) ? -1 : 1;
            });
        }

        return $migrations;
    }
}
