<?php

namespace Teleclinic\ApiPatient\Requests;

use Respect\Validation\Validator;


class MedicalIndexCreateRequest extends AbstractRequest
{
    /**
     * @param $input
     * @return $this
     */
    public function validate($input)
    {
        $rule = Validator::key('PATIENT_CODE', Validator::allOf(
                Validator::notEmpty()->setTemplate("Mã bệnh nhân không được để trống!"),
                Validator::stringType()->setTemplate("Mã bệnh nhân phải là kiểu chuỗi!")
            ))
            ->key('BMI', Validator::allOf(
                Validator::notEmpty()->setTemplate("Chỉ số chiều cao không được để trống!"),
                Validator::floatType()->setTemplate("Chỉ số chiều cao phải là kiểu số thực"),
                Validator::between(0, 250.0)->setTemplate("Chỉ số chiều cao phải nằm trong khoảng giữa 0 và 250.0!")
            ))
            ->key('BSA', Validator::allOf(
                Validator::notEmpty()->setTemplate("Chỉ số cân nặng không được để trống!"),
                Validator::floatType()->setTemplate("Chỉ số cân nặng phải là kiểu số thực"),
                Validator::between(0, 200.0)->setTemplate("Chỉ số cân nặng phải nằm trong khoảng giữa 0 và 200.0!")
            ))->setTemplate("Thiếu tham số đầu vào {{name}}!");
        parent::assert($rule, $input);
        return $this;
    }

///**
//* @return mixed|null
//*/
//    public function getMessages()
//    {
//        if ($this->valid)
//            return null;
//
//    }
}