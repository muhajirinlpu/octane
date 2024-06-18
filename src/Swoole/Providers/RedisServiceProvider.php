<?php

namespace Laravel\Octane\Swoole\Providers;

use Illuminate\Support\Arr;
use Laravel\Octane\Swoole\Providers\Redis\RedisManager;

class RedisServiceProvider extends \Illuminate\Redis\RedisServiceProvider
{
    public function register()
    {
        $this->app->singleton('redis', function ($app) {
            $config = $app->make('config')->get('database.redis', []);

            return new RedisManager($app, Arr::pull($config, 'client', 'phpredis'), $config);
        });

        $this->app->bind('redis.connection', function ($app) {
            return $app['redis']->connection();
        });
    }
}
