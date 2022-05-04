<?php

namespace Company\Kafka;

class KafkaClient
{
    static protected $config;

    function __construct()
    {
    }

    static function config($config)
    {
        static::$config = $config;
    }

    static function getConfig(){
        return static::$config;
    }
}
