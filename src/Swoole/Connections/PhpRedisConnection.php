<?php

namespace Laravel\Octane\Swoole\Connections;

class PhpRedisConnection extends \Illuminate\Redis\Connections\PhpRedisConnection
{
//    public function __construct($client, ?callable $connector = null, array $config = [])
//    {
//    }

    public function client()
    {
        return parent::client();
    }
}
