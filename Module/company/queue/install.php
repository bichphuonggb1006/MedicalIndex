<?php

use Company\Queue\Queue;

if (Queue::getDBType() == Queue::DB_TYPE_KAFKA){
    $topics = \Company\Queue\QueueKafkaProducer::getTopics();
    foreach ($topics as $topic => $partitions){
        \Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, $partitions, $topic);
    }
}
