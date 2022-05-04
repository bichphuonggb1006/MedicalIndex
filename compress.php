<?php

require_once __DIR__ . '/Docroot/index.php';

use Pacs\Storage\Lib\S3StorageManager;

$rootPath = "http://test:test1234@172.16.20.102:9000/testbucket";
$key = "multiframe.dcm ";
/**
 * Test compression
 */
$start = microtime(true);
var_dump(S3StorageManager::compress($rootPath, $key, "multiframe.dcm.jpeg", "DicomImageCompress", ["oxfer" => "jpeg", "quality" => 100]));
var_dump("Jpeg: ", microtime(true) - $start);

$start = microtime(true);
var_dump(S3StorageManager::compress($rootPath, $key, "multiframe.dcm.jpegls", "DicomImageCompress", ["oxfer" => "jpegls", "quality" => 100]));
var_dump("JpegLS: ", microtime(true) - $start);

$start = microtime(true);
var_dump(S3StorageManager::compress($rootPath, $key, "multiframe.dcm.jls", "DicomImageCompress", ["oxfer" => "jls", "quality" => 100]));
var_dump("Jpeg-LS: ", microtime(true) - $start);

$start = microtime(true);
var_dump(S3StorageManager::compress($rootPath, $key, "multiframe.dcm.j2k", "DicomImageCompress", ["oxfer" => "j2k", "quality" => 100]));
var_dump("J2k: ", microtime(true) - $start);

$start = microtime(true);
var_dump(S3StorageManager::compress($rootPath, $key, "multiframe.dcm.j2kls", "DicomImageCompress", ["oxfer" => "j2kls", "quality" => 100]));
var_dump("J2kLS: ", microtime(true) - $start);die;

/**
 * Test get metadata
 */
$start = microtime(true);
S3StorageManager::getMetadataDicom($rootPath, $key);
var_dump("S3 get metadata: ", microtime(true) - $start);

/**
 * Test modify
 */
$start = microtime(true);
var_dump(S3StorageManager::dcmModify($rootPath, $key, "multiframe.dcm.new", '{"modify" : {"PatientName" : "Nguyen A"}}' ));
var_dump("S3 modify: ", microtime(true) - $start);

/**
 * Test image
 */
$start = microtime(true);
file_put_contents("out.png", S3StorageManager::vrdcm2BufferImage($rootPath, $key, "PNG"));
var_dump("S3 image: ", microtime(true) - $start);