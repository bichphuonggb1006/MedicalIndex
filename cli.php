<?php

require_once __DIR__ . '/Docroot/index.php';

//\Pacs\Storage\Model\StorageMapper::makeInstance()->configRebalance("ONLINE");
//$inst = InstanceMapper::makeInstance()->getInstance("master", "1.2.840.113619.2.404.3.2831156996.875.1599094730.533.23", "1.2.840.113619.2.404.3.2831156996.875.1599094730.453.4");
//var_dump($inst);
//die;
//$arr = [];
//for($i=1; $i < 10000; $i++){
//    $str = strval($i);
//    var_dump($str);
////    $msg = [];
////    $msg["msg"]= $str;
////    $msg["key"]= $str;
////    $msg["keyPartition"]= $str;
////    $arr[] = $msg;
//    \Company\Queue\QueueProducer::makeInstance()->insertQueue("SYNC_ELASTIC", $str, $str, $str);
//    // $object1->sendMessage($str,$str, rand(0, 9));
//}

//\Company\Queue\QueueProducer::makeInstance()->insertManyQueues("test", json_decode(file_get_contents(__DIR__ . '/Docroot/data.json'), true));
//\Pacs\Ris\RisConnection::newSeries("asdasd");
//die();
//\Company\Queue\QueueProducer::makeInstance()
//    ->insertQueue("pacs_test", "gggggg", "aaaaaaa");

//\Pacs\Consumer\PacsConsumer::makeInstance()
//    ->subcribers(["PACS_STOW", "pacs_test"])
//    ->groupID("consumer2")
//    ->tasks([\Pacs\Consumer\PacsConsumer::STORE_VALIDATE])
//    ->run();

//$inFile = __DIR__ . '/Docroot/test.dcm';
//$outFile = __DIR__ . '/Docroot/series_attrs.modify.txt';
//
//$attrs = file_get_contents($inFile);
//
//echo json_encode(\Pacs\Dicom\DicomFile::bufferdump2json($attrs, strlen($attrs), "Full"));

//$modified = \Pacs\Dicom\DicomFile::vrbufferModify(file_get_contents($inFile), strlen(file_get_contents($inFile)), '{"modify":{"(0008,0060)":"MR"}}');
//var_dump(\Pacs\Dicom\DicomFile::bufferdump2json($modified, strlen($modified)));
//$res = \Pacs\Series\Model\SeriesElasticSearchMapper::makeInstance()
//    ->where("site_id", "5ffffd5d78d9c")
//    ->where("series_iuid", "1.2.840.113619.2.404.3.2831156996.920.1599267832.913.4198401")
//    ->getAll()
//    ["results"][0];
//$attrs= base64_decode($res["series_attrs"]);
//var_dump(\Pacs\Dicom\DicomFile::bufferdump2json($attrs, strlen($attrs)));

// echo "234234";
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "PACS_STOW");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "test");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "NEW_SERIES");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "NEW_INSTANCE");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "FILE_COMPLETED");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "SYNC_ELASTIC");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "REBALANCE_STORAGE");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "MOVE_NEARLINE_STORAGE");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "PROCESS");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "COMPRESSION_FILES");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "AUTO_COPY_FILE");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "UPLOAD_AI");
\Company\Kafka\KafkaTool::deleteTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "REMOVE_FILE");


\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "PACS_STOW");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "test");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "NEW_SERIES");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "NEW_INSTANCE");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "FILE_COMPLETED");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "SYNC_ELASTIC");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "REBALANCE_STORAGE");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "MOVE_NEARLINE_STORAGE");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "PROCESS");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "COMPRESSION_FILES");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "AUTO_COPY_FILE");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "UPLOAD_AI");
\Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], 1, 10, "REMOVE_FILE");


//$rootPath = "http://phpmed-fileserverapi/pacs/fileserver?auth=123123&root=/var/www/sharefolder";
//
//function testFolder($rootPath){
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::createFolder($rootPath, "folder"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::createFolder($rootPath, "folderDelete"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::folderSize($rootPath, "folder"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::copyFolder($rootPath, "folder", "folderCopy"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::viewFolder($rootPath, "", true));
//
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::deleteFolder($rootPath, "folderDelete"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::findFolder($rootPath, "folderDelete"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::renameFolder($rootPath, "folder", "folderRename"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::viewFolder($rootPath, "", true));
//}
//
//function testFile($rootPath) {
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::writeFile($rootPath, "file1.txt", "456456"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::renameFile($rootPath, "file1.txt", "file2.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::fileSize($rootPath, "file2.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::copyFile($rootPath, "file2.txt", "fileCopy.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::moveFile($rootPath, "file2.txt", "folderCopy/file2.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::findFile($rootPath, "folderCopy/file2.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::deleteFile($rootPath, "fileCopy.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::readBufferFile($rootPath, "folderCopy/file2.txt"));
//    var_dump(\Pacs\Storage\Lib\FileServerStorageManager::readChunkFile($rootPath, "file2.txt", 1, 3));
//}

//testFolder($rootPath);
//testFile($rootPath);

//var_dump(\Pacs\Storage\Lib\DirStorageManager::viewFolder("/var/www/html", false));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::createFolder("/var/www/html/Docroot/folder1"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::renameFolder("/var/www/html/Docroot/folder1", "/var/www/html/Docroot/folder2"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::folderSize("/var/www/html/Docroot"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::copyFolder("/root/folder3", "/var/www/html/Docroot/folder3"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::findFolder("/root/folder4"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::moveFolder('/root/folder3', "/var/www/html/Docroot/folder3"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::deleteFolder("/var/www/html/Docroot/folder1"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::writeFile("/var/www/html/Docroot/file1.txt", "456456"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::renameFile("/var/www/html/file2.txt", "/var/www/html/Docroot/file2.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::fileSize("/var/www/html/Docroot/file2.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::copyFile("/var/www/html/Docroot/file2.txt", "/var/www/html/file2.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::moveFile("/var/www/html/file2.txt", "/root/file2.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::findFile("/root/file3.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::deleteFile("/root/file2.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::readBufferFile("/var/www/html/Docroot/file1.txt"));
//var_dump(\Pacs\Storage\Lib\DirStorageManager::readChunkFile("/var/www/html/Docroot/file1.txt", 7, 3));

//var_dump(\Pacs\Series\Model\SeriesMapper::makeInstance()->getSeriesByStudyIUID("master", "1.3.12.2.1107.5.1.4.63424.30000020052406270357800000004"));
//var_dump(\Pacs\Instance\Model\InstanceMapper::makeInstance()->getInstancesBySeriesIUID("master", "1.3.12.2.1107.5.1.4.63424.30000020052406080293700000061"));

//var_dump(\Pacs\Storage\Lib\StorageFunction::moveFileStorageClass("401cd7bcadd69bd8587b41d6b9ed521e", "ONLINE", "NEARLINE"));