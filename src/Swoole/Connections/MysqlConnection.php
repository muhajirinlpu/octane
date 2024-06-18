<?php

namespace Laravel\Octane\Swoole\Connections;

use Laravel\Octane\Swoole\Coroutines\MysqlConnectionPool;

class MysqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    public function getPdo()
    {
        return MysqlConnectionPool::getMysql($this->getConfig());
    }

    public function getReadPdo()
    {
        return MysqlConnectionPool::getMysql($this->getConfig());
    }
}
