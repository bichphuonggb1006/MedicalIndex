<?php

namespace Company\File;
use Company\File\Model\FileMapper;
use Company\Telehealthservice\Model\ServiceDirMapper;
use Company\VrZip\File;
use Teleclinic\ApiPatient\Auth\Auth;
use Company\S3\S3;
use Teleclinic\Teleclinic\Model\PatientMapper;

class FileController extends \Company\MVC\Controller
{
    public function upload()
    {
        $rs = new \Result();
        $type = $this->req->post('type');
        if (!in_array($type, array_keys(FileMapper::$allType))) {
            $rs->setErrors('Truy cập không hợp lệ', ['error' => 'Type is invalid'], 404);
            return $this->outputJSON($rs);
        }
        ini_set('post_max_size', '30M');
        ini_set('upload_max_filesize', '30M');
        switch ($type){
//            case 'avatar':
//                $rs=  $this->uploadAvatar($patient);
//                break;
            case FileMapper::CONTEXT_SERVICE_DIR:
                $rs = $this->updateServiceDir();
                break;
            case FileMapper::CONTEXT_SERVICE_LIST:
                $rs = $this->updateServiceList();
                break;
            case FileMapper::CONTEXT_SITE:
                $rs = $this->updateSite();
                break;
            default:
                break;
        }

        return $this->outputJSON($rs);
    }


    private function uploadAvatar($patient){
        $rs = new \Result();
        $fileName = trim($_FILES['file']['name']); //4 XĐ tên file
        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        $path = '/avatars/' . date('Ymd') . '/' . uniqid() . '.' . $ext;
        try {
            $rs = (new S3())
                ->setMaxSize(30)
                ->setAllowedExt(['png', 'jpeg', 'jpg'])
                ->pushObject($_FILES, $path);
            if ($rs->isOk()) {
                FileMapper::makeInstance()->insert([
                    'id' => uid(),
                    'createdDate' => date('Y-m-d H:i:s'),
                    'context' => PatientMapper::class . '::' . $patient->id,
                    'name' => $fileName,
                    'mime' => $ext,
                    'size' => $_FILES['file']['size'],
                    'resource' => $rs->getValueInDataByKey('path'),
                    'response' => $rs->getValueInDataByKey('rs_push_s3'),
                ]);
            }

        } catch (\Exception $exception) {
            $rs->setErrors($exception->getMessage());
        }
        return $rs;
    }
    private function updateServiceDir(){
        $rs = new \Result();
        $fileName = trim($_FILES['file']['name']); //4 XĐ tên file
        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        $path = '/serviceDir/' . date('Ymd') . '/' . uniqid() . '.' . $ext;
        try {
            $rs = (new S3())
                ->setMaxSize(30)
                ->setAllowedExt(['png', 'jpeg', 'jpg'])
                ->pushObject($_FILES, $path);
            if ($rs->isOk()) {

                    FileMapper::makeInstance()->insert([
                        'id' => uid(),
                        'createdDate' => date('Y-m-d H:i:s'),
                        'name' => $fileName,
                        'mime' => $ext,
                        'size' => $_FILES['file']['size'],
                        'resource' => $rs->getValueInDataByKey('path'),
                        'response' => $rs->getValueInDataByKey('rs_push_s3'),
                    ]);

            }

        } catch (\Exception $exception) {
            $rs->setErrors($exception->getMessage());
        }
        return $rs;
    }

    private function updateServiceList(){
        $rs = new \Result();
        $fileName = trim($_FILES['file']['name']); //4 XĐ tên file
        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        $path = '/serviceList/' . date('Ymd') . '/' . uniqid() . '.' . $ext;
        try {
            $rs = (new S3())
                ->setMaxSize(30)
                ->setAllowedExt(['png', 'jpeg', 'jpg'])
                ->pushObject($_FILES, $path);
            if ($rs->isOk()) {

                FileMapper::makeInstance()->insert([
                    'id' => uid(),
                    'createdDate' => date('Y-m-d H:i:s'),
                    'name' => $fileName,
                    'mime' => $ext,
                    'size' => $_FILES['file']['size'],
                    'resource' => $rs->getValueInDataByKey('path'),
                    'response' => $rs->getValueInDataByKey('rs_push_s3'),
                ]);

            }

        } catch (\Exception $exception) {
            $rs->setErrors($exception->getMessage());
        }
        return $rs;
    }

    private function updateSite(){
        $rs = new \Result();
        $fileName = trim($_FILES['file']['name']); //4 XĐ tên file
        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
        $path = '/siteList/' . date('Ymd') . '/' . uniqid() . '.' . $ext;
        try {
            $rs = (new S3())
                ->setMaxSize(30)
                ->setAllowedExt(['png', 'jpeg', 'jpg'])
                ->pushObject($_FILES, $path);
            if ($rs->isOk()) {

                FileMapper::makeInstance()->insert([
                    'id' => uid(),
                    'createdDate' => date('Y-m-d H:i:s'),
//                        'context' => ServiceDirMapper::class . '::' . $serviceDir->id,
                    'name' => $fileName,
                    'mime' => $ext,
                    'size' => $_FILES['file']['size'],
                    'resource' => $rs->getValueInDataByKey('path'),
                    'response' => $rs->getValueInDataByKey('rs_push_s3'),
                ]);

            }

        } catch (\Exception $exception) {
            $rs->setErrors($exception->getMessage());
        }
        return $rs;
    }

    public function show()
    {
//        $patient = Auth::makeInstance()->requireLogin()->user();
        $rs = new \Result();
        $path = $this->req->get('path');
        if (trim($path) == '') {
            $rs->setErrors('Truy cập không hợp lệ!!!');
            return $this->outputJSON($rs);
        }
        $s3Attachment = (new S3())->get($path);
        $objectResponse = $s3Attachment->getData();
        header('Content-Description: File Transfer');
        //this assumes content type is set when uploading the file.
        header('Content-Type: ' . $objectResponse['ContentType']);
        header('Content-Disposition: attachment; filename=' . '6242e59595897.png');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        echo $objectResponse['Body'];
    }
}