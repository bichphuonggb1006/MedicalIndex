<?php

namespace Company\Queue;

abstract class QueueConsumer extends Queue
{
    protected $consumer;

    function __construct()
    {

    }


    /**
     * @return array string
     */
    abstract function subcribers();

    /**
     * @return string
     */
    abstract function groupID();

    static function makeInstance()
    {
        return new static;
    }

    /**
     * make KafkaConsumer
     * @return KafkaConsumer
     */
    function makeKafka()
    {
        return \Company\Kafka\KafkaConsumer::makeInstance()
            ->topicsName($this->subcribers())
            ->groupID($this->groupID())
            ->autoCommit(false)
            ->resetOffset("earliest")
            ->makeConsumer();
    }

    function makeSql()
    {
        return Null;
    }

    /**
     * run consumer
     */
    function run($maxRecords = 1)
    {
        if (static::getDBType() == "KAFKA") {
            $this->consumer = $this->makeKafka();
        } else {
            $this->consumer = $this->makeSql();
        }

        $status = 0;
        while (true) {
            if (static::getDBType() == "KAFKA") {
                $arrMsg = json_decode($this->consumer->getMessages($maxRecords), true);
                if ($arrMsg == Null){
                    var_dump($arrMsg);
                    var_dump("______________________________");
                    usleep(500);
                    if ($status < 6){
                        $status++;
                    }
                    if ($status == 5){
                        $this->pause();
                    }
                    continue;
                }
                if ($status != 0){
                    $status = 0;
                    $this->resume();
                }
                $start_time = microtime(true);
                while (true) {
                    if($this->processQueue($arrMsg)){
                        $this->consumer->commitOffset();
                        break;
                    }else{
                        sleep(1);
                    }

                    $end_time = microtime(true);
                    $execution_time = ($end_time - $start_time);
                    if($execution_time >= 120){
                        var_dump("retry message");
                        $this->retry($arrMsg);
                        $this->consumer->commitOffset();
                        break;
                    }
                }
            } else {

            }
        }
    }

    function retry($arrMsg)
    {
        foreach ($arrMsg as $msg) {
            QueueProducer::makeInstance()->insertQueueWithPartition($msg["Topic"], $msg["Partition"], $msg["Key"], $msg["Value"]);
        }
    }

    /**
     * @param $arrMsg
     * @return boolean
     */
    abstract function processQueue($arrMsg);

    function resume()
    {
        var_dump("resume");
        \Company\Cassandra\Mapper::connect();
        \Company\SQL\DB::Connect();
    }

    function pause()
    {
        var_dump("pause");
        \Company\Cassandra\Mapper::closeDB();
        \Company\SQL\DB::Disconnect();
    }

}