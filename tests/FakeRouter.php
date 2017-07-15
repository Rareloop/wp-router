<?php

namespace Rareloop\WordPress\Router\Test;

use Rareloop\WordPress\Router\Router;

class FakeRouter extends Router
{
    private static $shutdownCallback;

    public static function reset()
    {
        static::$singleton = null;
    }

    public static function setShutdownCallback($callback)
    {
        static::$shutdownCallback = $callback;
    }

    protected static function shutdown()
    {
        if (is_callable(static::$shutdownCallback)) {
            call_user_func(static::$shutdownCallback);
        }
    }
}
