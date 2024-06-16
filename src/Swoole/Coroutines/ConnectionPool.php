<?php

namespace Laravel\Octane\Swoole\Coroutines;

use Illuminate\Database\Connectors\MySqlConnector;

class ConnectionPool
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

        $pcid = \Co::getPcid();

        if (isset(self::$pairList[$pcid])) {
            return self::$pairList[$pcid];
        }

        self::$pairList[$pcid] = self::$mysqlPool[$dsn]->get();

        defer(function () use ($pcid, $dsn) {
            self::$mysqlPool[$dsn]->put(self::$pairList[$pcid]);
            unset(self::$pairList[$pcid]);
        });

        return self::$pairList[$pcid];
    }
}
