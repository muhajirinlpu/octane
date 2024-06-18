<?php

namespace Laravel\Octane\Swoole\Coroutines;

use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Log;
use Laravel\Octane\Swoole\Providers\Redis\RedisManager;

class RedisConnectionPool
{
    /**
     * name as a key
     *
     * @var array<string, \Swoole\ConnectionPool>
     */
    protected static array $redisPool = [];

    /**
     * @var array<int, Connection>
     */
    protected static array $pairList = [];

    public static function getRedis(string $name, RedisManager $manager): Connection
    {
        if (! isset(self::$redisPool[$name])) {
            self::$redisPool[$name] = new \Swoole\ConnectionPool(function () use ($name, $manager) {
                return $manager->resolve($name);
            });
        }

        $cid = \Co::getCid();

        if (isset(self::$pairList[$cid])) {
            $pdo = self::$pairList[$cid];

            return $pdo;
        }

        self::$pairList[$cid] = self::$redisPool[$name]->get();

        defer(function () use ($cid, $name) {
            $pdo = self::$pairList[$cid];
            self::$redisPool[$name]->put($pdo);

            unset(self::$pairList[$cid]);
        });

        return self::$pairList[$cid];
    }
}
