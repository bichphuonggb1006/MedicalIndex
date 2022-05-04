<?php


namespace Company\ElasticSearch\Lib;


use Company\ElasticSearch\ElasticMapper;
use Company\Queue\QueueConsumer;

class ConsumerSyncElastic extends QueueConsumer
{

    function subcribers()
    {
        // TODO: Implement subcribers() method.
        return ["SYNC_ELASTIC"];
    }

    function groupID()
    {
        // TODO: Implement groupID() method.
        return "ConsumerSyncElastic";
    }

    function processQueue($arrMsg)
    {
        // TODO: Implement processQueue() method.
        var_dump("________________Begin Sync DB________________");
        var_dump($arrMsg);
        $completed = true;

        try {
            // xu ly queue
            $completed = ElasticMapper::makeInstance()->handleMultiMessagess($arrMsg);
        }catch (\Exception $ex){
            var_dump($ex->getMessage());
            $completed = false;
        }

        var_dump("________________END Sync DB________________");
        var_dump("________________Success $completed ________________");
        return $completed;
    }
}