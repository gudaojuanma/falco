<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Asset extends Model
{
    const REDIS_CACHE_KEY = 'falco.assets';

    public $id;

    public $hash;

    public $path;

    public function initialize()
    {
        $this->setSource('assets');
    }

    public static function cache()
    {
        $redis = resolve('redis');

        resolve('redis')->del(self::REDIS_CACHE_KEY);

        foreach (Asset::find() as $asset) {
            $redis->hset(self::REDIS_CACHE_KEY, $asset->path, $asset->hash);
        }
    }

    /**
     * 根据路径获取hash
     * @param $path
     * @return int|mixed
     */
    public static function getHashByPath($path)
    {
        $redis = resolve('redis');

        if ($redis->hexists(self::REDIS_CACHE_KEY, $path)) {
            return $redis->hget(self::REDIS_CACHE_KEY, $path);
        }

        if (($asset = self::findFirstByPath($path))) {
            return $asset->hash;
        }

        $fullPath = public_path('assets/' . $path);
        if (file_exists($fullPath)) {
            return hash_file('sha256', $fullPath);
        }

        return time();
    }


}