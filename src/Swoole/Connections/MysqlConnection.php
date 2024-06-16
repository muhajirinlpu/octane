<?php

namespace Laravel\Octane\Swoole\Connections;

use Laravel\Octane\Swoole\Coroutines\ConnectionPool;

class MysqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    public function getPdo()
    {
        return ConnectionPool::getMysql($this->getConfig());
    }

    public function getReadPdo()
    {
        return ConnectionPool::getMysql($this->getConfig());
    }
}
