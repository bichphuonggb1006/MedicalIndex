<?php

namespace Company\S3;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Company\MVC\Bootstrap;
use Company\VrZip\Exception;
use GuzzleHttp\Client;

class S3
{
    /**
     * @var int $maxsite MB
     */
    protected $maxSize = 30;

    protected $fileUpload = null;

    protected $allowedExt = [];
    protected $s3Config = null;

    public function __construct()
    {
        $s3Confif = arrData(Bootstrap::getInstance()->config, 's3');
        if (!$s3Confif)
            throw new Exception('Cấu hình kết nối S3 không hợp lệ');
        $this->s3Config = $s3Confif;
    }

    public function setAllowedExt(array $allowedExt = [])
    {
        foreach ($allowedExt as &$item) {
            $item = strtoupper($item);
        }
        $this->allowedExt = $allowedExt;
        return $this;
    }

    /**
     * @param int $maxSize
     * @return $this
     */
    public function setMaxSize(int $maxSize = 30)
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function validMaxSite()
    {
        $file = $this->fileUpload;
        $fileSize = $file['size'] / 1024 / 1024;
        if ($fileSize > $this->maxSize)
            throw new Exception('Dung lượng file không hợp lệ');
        return $this;
    }

    public function validAllowedExt()
    {
        $file = $this->fileUpload;
        $allowedExt = $this->allowedExt;
        if (count($allowedExt) == 0)
            return $this;
        $fileName = trim($file['name']); //4 XĐ tên file
        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        if (!in_array(strtoupper($ext), $allowedExt)) {
            throw new Exception('Không đúng định dạng file attachments:' . implode($allowedExt, ','));
        }

    }

    /**
     * @param $_FILE $file
     * @param $path_storage
     * @return \Result|null
     * @throws Exception
     */
    public function pushObject($file, $path_storage): ?\Result
    {
        $path_storage = rtrim($path_storage,'\\');
        $rs = new \Result();
        if (empty($file) || !arrData($file, 'file') || trim($file['file']['name']) == '') {
            return $rs->setErrors('Tập tin không hợp lệ', [], 422);
        }
        $this->fileUpload = $file['file'];
        $rs = new \Result();

        $s3Conf = $this->s3Config;
        try {
            $this->validMaxSite();
            $this->validAllowedExt();
        } catch (\Exception $exception) {
            return $rs->setErrors($exception->getMessage(), [], 422);
        }
        try {
            //create S3 client
            $s3 = new S3Client(
                [
                    'version' => 'latest',
                    'region' => 'asia-southeast1-a',
                    'endpoint' => arrData($s3Conf, 'ip', ''),
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key' => arrData($s3Conf, 'access_key'),
                        'secret' => arrData($s3Conf, 'secret_key'),
                    ],
                    'http' => [
                        "connect_timeout" => 10,
                        "timeout" => 10,
                    ]
                ]
            );
            $data = file_get_contents($this->fileUpload['tmp_name']);
//            $base64 = 'data:' . $this->fileUpload['type'] . ';base64,' . base64_encode($data);
            $resp = $s3->putObject([
                'Bucket' => arrData($s3Conf, 'bucket', ''),
                'Key' => $path_storage,
                'Body' => $data,
                'ACL' => 'public-read'
            ]);
            $rs->setSuccess([
                'path' => $path_storage
            ]);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            $rs->setErrors('Xảy ra sự cố không xác định', ['exception' => $e->getMessage()]);
        }
        return $rs;
    }

    /**
     * @param string $source
     * @return array
     */
    public function get(string $source): ?\Result
    {
        $rs = new \Result();
        $s3Conf = $this->s3Config;

        try {
            //create S3 client
            $s3 = new S3Client(
                [
                    'version' => 'latest',
                    'region' => 'asia-southeast1-a',
                    'endpoint' => arrData($s3Conf, 'ip', ''),
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key' => arrData($s3Conf, 'access_key'),
                        'secret' => arrData($s3Conf, 'secret_key'),
                    ],
                    'http' => [
                        "connect_timeout" => 10,
                        "timeout" => 10,
                    ]
                ]
            );
            $objectResponse = $s3->getObject([
                'Bucket' => arrData($s3Conf, 'bucket', ''),
                'Key' => $source
            ]);
            return $rs->setSuccess($objectResponse);
        } catch (S3Exception $e) {
            $rs->setErrors('Xảy ra sự cố trong quá trình xử lý', [
                'detail' => $e->getMessage()
            ]);
        }
        return $rs;
    }
}