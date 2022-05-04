<?php

namespace Company\Log;

use Fluent\Logger\FluentLogger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class FluentdHandler extends AbstractProcessingHandler
{
    private $fluentLogger;

    public function __construct($host, $port, $level = Logger::DEBUG, $bubble = true)
    {
        $this->fluentLogger = FluentLogger::open($host, $port);
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $this->fluentLogger->post(
            $record['channel'],
            [
                'level' => $record['level_name'],
                'message' => json_decode($record['message'], true) ?: $record['message'],
                'time' => $record['datetime']->format(DATE_ISO8601),
                'channel' => $record['channel']
            ]
        );
    }
}