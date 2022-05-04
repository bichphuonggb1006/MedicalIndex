<?php

require_once __DIR__ . '/Docroot/index.php';

//\Pacs\Ris\RisConnection::newSeries("asdasd");
//die();
//\Company\Queue\QueueProducer::makeInstance()
//    ->insertQueue("pacs_test", "gggggg", "aaaaaaa");

\Pacs\Consumer\PacsConsumer::makeInstance()
    ->subcribers(["FILE_COMPLETED"])
    ->groupID("ConsumerMoveFile")
    ->tasks([\Pacs\Consumer\PacsConsumer::MOVE_NEARLINE_STORAGE])
    ->run();