<?php

use Company\Cache\PhpFileCache;
use Company\Cache\Redis;
use Company\Cassandra\Mapper;
use Company\Kafka\KafkaClient;
use Company\Queue\Queue;
use Company\Log\Logger;
use Company\Zone\Model\ZoneMapper;
use Company\Cache;

/**
 * 0 = tắt
 * 1 = PHP, Database trừ trên service
 * 10 = tất cả
 */
$exports['debugMode'] = 1;

$exports['production'] = 0;

$exports['logger'] = [
    'fileLogger' => [
        'name' => 'pacs2',
        'path' => __DIR__ . '/../../log/app.log',
        'level' => \Monolog\Logger::INFO,
        'maxSize' => 5242880,   // rotate log if exceeds 100Kb
        'maxFiles' => 20
    ],
    'tcpLogger' => [

    ]
];

Logger::setConfig($exports['logger']);
Logger::setInstance(Logger::$FILE_LOGGER);

const CEPH_DIR = BASE_DIR . "/Config/Ceph";

//kết nối database
$exports['db'] = [
    'default' => [
        'type' => 'mysqli',
        'hosts' => ['172.16.20.145:3306'], //multiple ip for galera cluster
        'name' => 'pacs',
        'user' => 'root',
        'pass' => 'vietrad123'
    ]
];

#Thời gian hiệu lực mã otp xác thực khi đăng ký tài khoản
$exports['otp'] = [
    'expire_time_verify_otp_active_patient' => time() + 60,
];

##Thông tin kết nối service gửi notification
$exports['serviceNotification'] = [
    'base_uri' => 'http://service-notification.test',
    'access_key' => 'huongpv',
    'secret_key' => 'huongpv@2022',
];
##


$exports['jwtPatient'] = [
    "iss" => "http://teletealth.test",
    "aud" => "http://teletealth.test",
    'secret' => 'hello',
    'exp' => 30 * 24 * 60 * 60
];



$exports['elastic'] = [
    'hosts' => [
        "elastic:MyElasticPassword_678@172.16.20.133:9200",
//        "172.16.10.105:9200"
    ],
    'retries' => 2
];
\Company\ElasticSearch\DB::config($config["elastic"]);

$exports['cassandra'] = [
    'host' => [
        '172.16.10.245'
    ],
    'user' => null,
    'password' => null,
    'keyspace' => 'pacs'
];

//set DB type: SQL or CQL
DbAdapterMapper::setDBType("CQL");

$exports['redis'] = [
    "host" => "172.16.20.112",
    "port" => 6379,
    "timeout" => 1, // float, value in seconds (optional, default is 0 meaning unlimited)
    "reserved" => null, // should be NULL if retry_interval is specified
    "retry_interval" => 100, // int, value in milliseconds (optional), 100ms delay between reconnection attempts.
    "read_timeout" => 0, // float, value in seconds (optional, default is 0 meaning unlimited),
    "auth" => "Vrad@123" // password
//    "auth" => ['user' => 'phpredis', 'pass' => 'phpredis']
];

// config Cache
Redis::setConfig($exports['redis']);
(new Redis())->ping(); // test redis

PhpFileCache::setCachePath(sys_get_temp_dir());

\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::SHARE_CACHE, new Redis());
//\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::SHARE_CACHE, new \Company\Cache\APCU());
\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::PRIVATE_MEMORY_CACHE, new \Company\Cache\APCU());
\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::PRIVATE_CACHE, new PhpFileCache());


//$exports['kafka'] = [
//    "configs" => [
//        'metadata.broker.list' => '172.16.20.133:9092',
//        'security.protocol' => 'SASL_PLAINTEXT',
//        'sasl.mechanism' => 'PLAIN',
//        'sasl.username' => 'admin',
//        'sasl.password' => 'admin-secret'
//    ],
//    "topics" => [
//        "PACS_STOW" => 10,
//        "NEW_SERIES" => 10,
//        "NEW_INSTANCE" => 10,
//        "SYNC_ELASTIC" => 10,
//        "REBALANCE_STORAGE" => 10,
//        "PROCESS" => 10,
//        "MOVE_NEARLINE_STORAGE" => 10,
//        "AUTO_COPY_FILE" => 10,
//        "UPLOAD_AI" => 10,
//        "REMOVE_FILE" => 10
//    ]
//];

$exports['tmp'] = [
    'local' => '/tmp/local/',
    'shared' => '/tmp/share/'
];
if (!file_exists($exports['tmp']['local'])) {
    mkdir($exports['tmp']['local'], 0777, true);
}

$exports['cryptSecret'] = 'abM)(*2312';

date_default_timezone_set('Asia/Bangkok');

/*// kafka config
if (isset($exports['kafka'])) {
    KafkaClient::config(
        $exports['kafka']['configs']
    );
    Queue::enableKafka($exports['kafka']['topics']);
}*/