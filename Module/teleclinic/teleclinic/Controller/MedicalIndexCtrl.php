<?php

namespace Teleclinic\Teleclinic\Controller;

use Company\Auth\Auth;
use Company\MVC\Layout;
use Company\MVC\Module;
use http\Env\Request;
use Respect\Validation\Rules\AllOf;
use Respect\Validation\Rules\Between;
use Respect\Validation\Rules\FloatType;
use Respect\Validation\Rules\IntType;
use Respect\Validation\Rules\Max;
use Respect\Validation\Rules\NotEmpty;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Requests\PatientRegisterRequest;
use Teleclinic\Teleclinic\Model\MedicalIndexMapper;
use Teleclinic\ApiPatient\Requests\MedicalIndexCreateRequest;
use Teleclinic\ApiPatient\Requests\MedicalIndexUpdateRequest;
class MedicalIndexCtrl extends \Company\MVC\Controller
{
    function getAll()
    {
        $rs = new \Result();
        $pageNo = $this->req->get('pageNo', 1);
        $pageNo= $pageNo>0 ? $pageNo : 1;
        $pageSize = $this->req->get('pageSize', 5);
        $pageSize = $pageSize>0 ? $pageSize : 5;
        $medicalindexs = MedicalIndexMapper::makeInstance()
            ->setPage($pageNo, $pageSize)
            ->getPage();
        if($medicalindexs)
        {
            $rs->setSuccess($medicalindexs);
        }
        else
        {
            $rs->setErrors('Lỗi', [], 500);
        }
        return $this->outputJSON($rs->toArray());
    }
    function getID($id)
    {
        if (!MedicalIndexMapper::makeInstance()->filterID($id)->isExists())
        {
            return $this->outputJSON('ID không tồn tại');
        }
        else
        {
            $rs = new \Result();
            $medicalindex = MedicalIndexMapper::makeInstance()->filterID($id)->getEntityOrFail();
            if ($medicalindex)
            {
                $rs->setSuccess($medicalindex);
            }
            else
            {
                $rs->setErrors('Lỗi', [], 500);
            }
            return $this->outputJSON($rs->toArray());
        }
    }
    function create()
    {
        $rs = new \Result();
        $arr = $this->input();
        $validator = (new MedicalIndexCreateRequest())->validate($this->input());
        if (!$validator->isValid()) {
            $rs->setErrors('Lỗi!!!', $validator->getMessages(), 422);
            return $this->outputJSON($rs->toArray());
        }
        $patientcode = $arr['PATIENT_CODE'];
        $bmi = $arr['BMI'];
        $bsa = $arr['BSA'];
        $data = [
            'BMI' => $bmi,
            'BSA' => $bsa
        ];
        if (MedicalIndexMapper::makeInstance()->filterPatientCode($patientcode)->isExists())
        {
            return $this->outputJSON('Mã bệnh nhân đã tồn tại');
        }
        else
        {
            $arrInput = [
                'patient_code' => $patientcode,
                'type' => "BMI_BSA",
                'dData' => json_encode($data),
                'createdAt' => \DateTimeEx::create()->toIsoString(true)
            ];
            $create = MedicalIndexMapper::makeInstance()->insert($arrInput);
            if($create)
            {
                $id = MedicalIndexMapper::makeInstance()->db->insert_Id();
                $rs->setSuccess([
                    'id' => $id
                ]);
            }
            else
            {
                $rs->setErrors('Lỗi', [], 500);
            }
            return $this->outputJSON($rs->toArray());
        }
    }
    function update($id)
    {
        if (!MedicalIndexMapper::makeInstance()->filterID($id)->isExists())
        {
            return $this->outputJSON('ID không tồn tại');
        }
        else
        {
            $rs = new \Result();
            $arrInput = $this->input();
            $validator = (new MedicalIndexUpdateRequest())->validate($this->input());
            if (!$validator->isValid()) {
                $rs->setErrors('Lỗi!!!', $validator->getMessages(), 422);
                return $this->outputJSON($rs->toArray());
            }
            $bmi = $arrInput['BMI'];
            $bsa = $arrInput['BSA'];
            $data = [
                'BMI' => $bmi,
                'BSA' => $bsa
            ];
            $updatemedicalindex = [
                'dData' => json_encode($data),
                'createdAt' => \DateTimeEx::create()->toIsoString(true)
            ];
            MedicalIndexMapper::makeInstance()->filterID($id)->update($updatemedicalindex);
            $result = MedicalIndexMapper::makeInstance()->filterID($id)->getEntityOrFail();
            if ($result)
            {
                $rs->setSuccess($result);
            }
            else
            {
                $rs->setErrors('Lỗi', [], 500);
            }
            return $this->outputJSON($rs->toArray());
        }
    }
    function delete($id)
    {
        if(!MedicalIndexMapper::makeInstance()->filterID($id)->isExists())
        {
            return $this->outputJSON('ID không tồn tại');
        }
        else
        {
            $rs = new \Result();
            $result = MedicalIndexMapper::makeInstance()->filterID($id)->delete();
            if ($result)
            {
                $rs->setSuccess([
                    "message" => "Đã xóa thành công!"
                ]);
            }
            else
            {
                $rs->setErrors('Lỗi', [], 500);
            }
            return $this->outputJSON($rs->toArray());
        }
    }
}