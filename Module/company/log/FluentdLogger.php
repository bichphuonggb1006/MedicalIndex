<?php

namespace Company\Log;

use Monolog\Logger;
use Company\Log\Logger as VrLogger;

class FluentdLogger
{
    static $configKey = "fluentdLogger";

    public static function makeInstance($channel): Logger
    {
        $config = VrLogger::$config[self::$configKey];
        $handler = new FluentdHandler($config['host'], $config['port']);
        $logger = new Logger($channel, $config['level']);
        $logger->pushHandler($handler);
        return $logger;
    }
}