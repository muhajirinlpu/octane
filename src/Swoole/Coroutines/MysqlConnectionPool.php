<?php

namespace Laravel\Octane\Swoole\Coroutines;

use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Support\Facades\Log;

class MysqlConnectionPool
{
    /**
     * dsn as a key
     *
     * @var array<string, \Swoole\ConnectionPool>
     */
    protected static array $mysqlPool = [];

    /**
     * @var array<int, \PDO>
     */
    protected static array $pairList = [];

    public static function getMysql(array $config): \PDO
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];

        $dsn = "mysql:host={$host};port={$port};dbname={$database}";

        if (! isset(self::$mysqlPool[$dsn])) {
            self::$mysqlPool[$dsn] = new \Swoole\ConnectionPool(function () use ($config) {
                $connector = new MySqlConnector();

                return $connector->connect($config);
            });
        }

        $cid = \Co::getCid();

        if (isset(self::$pairList[$cid])) {
            $pdo = self::$pairList[$cid];

            return $pdo;
        }

        self::$pairList[$cid] = self::$mysqlPool[$dsn]->get();

        defer(function () use ($cid, $dsn) {
            $pdo = self::$pairList[$cid];
            self::$mysqlPool[$dsn]->put($pdo);

            unset(self::$pairList[$cid]);
        });

        return self::$pairList[$cid];
    }
}
