<?php

namespace Company\Log;

use Company\ElasticSearch\ElasticMapper;
use Monolog\Logger;
use Company\Log\Logger as VrLogger;

class ElasticLogger
{
    static $configKey = "elasticLogger";

    public static function makeInstance($channel): Logger
    {
        $config = VrLogger::$config[self::$configKey];
        $handler = new ElasticsearchHandler(ElasticMapper::makeInstance()->getConn(), ["index" => $config["index"]]);
        $logger = new Logger($channel, $config["level"]);
        $logger->pushHandler($handler);
        return $logger;
    }
}