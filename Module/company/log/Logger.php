<?php

namespace Company\Log;

class Logger {
    const FILE_LOGGER = 1;
    const ELASTIC_LOGGER = 2;
    const FLUENTD_LOGGER = 3;

    public static $config;

    public static function setConfig($conf)
    {
        static::$config = $conf;
    }

    public static function makeLogger($loggerType, $channel)
    {
        switch ($loggerType) {
            case static::FILE_LOGGER:
                return FileLogger::makeInstance($channel);
            case static::ELASTIC_LOGGER:
                return ElasticLogger::makeInstance($channel);
            case static::FLUENTD_LOGGER:
                return FluentdLogger::makeInstance($channel);
        }
    }
}