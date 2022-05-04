<?php

namespace Teleclinic\ApiPatient\Model;

use Company\Entity\Entity;
use Company\SQL\Mapper;
use Respect\Validation\Rules\Callback;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Rules\Phone;
use Respect\Validation\Rules\StringType;
use Respect\Validation\Validator;
use Teleclinic\Teleclinic\Model\AbstractMapper;
use Teleclinic\Teleclinic\Model\OtpMapper;
use Teleclinic\Teleclinic\Model\PatientAttrMapper;
use Teleclinic\Teleclinic\Model\PatientAttrs\AbstractPatientAttr;
use Teleclinic\Teleclinic\Model\PatientMapper;

/**
 * @property int $id
 * @property int $code
 * @property string $cardID
 * @property string $birthDate
 * @property string $fullName
 * @property string $gender
 */
class ApiPatientPatientMapper extends \Teleclinic\Teleclinic\Model\PatientMapper
{
    /**
     * @param Entity $patientEntity
     * @param array $patientData
     * @param string|null $patientID
     * @throws \Exception
     */
    public function store(Entity $patientEntity, array $data): \Result
    {
        /**
         * @var PatientMapper $patientEntity
         */
        $response = new \Result();
        $attrErrors = [];
        $this->startTrans();
        foreach (ApiPatientPatientAttrMapper::$attrsKey as $attrKey => $attrClass) {
            /**
             * @var AbstractPatientAttr $instanceAttr
             */
            $instanceAttr = new $attrClass;
            $instanceAttr->validate($data[$attrKey]);
            if (!$instanceAttr->isValid()) {
                $attrErrors[$attrKey] = $instanceAttr->getMessages()[$attrKey] ?? 'System error';
            } else {
                PatientAttrMapper::makeInstance()
                    ->filterPatientCode($patientEntity->code)
                    ->filterAttrCode($attrKey)
                    ->update([
                        'attrValue' => $data[$attrKey]
                    ]);
            }
        }
        if (count($attrErrors)) {
            $this->rollbackTrans();
            return $response->setErrors('Param is invalid', $attrErrors, 422);
        }
        ApiPatientPatientMapper::makeInstance()->filterID($patientEntity->id)->filterCode($patientEntity->code)->update($data);
        $this->completeTransOrFail();
        return $response->setSuccess();

        return false;


//        $patientValidator = $this->checkValidate($patientData);
//        if ($patientValidator['status'] === false) {
//            return $patientValidator;
//        }
//        $patientData = $patientValidator['data'];
//        $patientID = uid();
//        $patientData += [
//            'id' => $patientID,
//            'code' => uid(),
//            'status' => PatientMapper::STATUS_NEW
//        ];
//        //Sent otp
//        $expireOtp = time() + 90;
//        $sendOtp = OtpMapper::makeInstance()->store($patientData['phone'], OtpMapper::TYPE_PATIENT_REGISTER, $patientID, $expireOtp, true);
//        if ($sendOtp['status'] == false)
//            return result(false, $sendOtp['data'], 500);
//        $this->startTrans();
//        if (\Teleclinic\Teleclinic\Model\PatientMapper::makeInstance()->insert($patientData) === false)
//            throw new \Exception('Save patient failed');
//        $password = trim($patientData['password']) == '' ? $patientData['phone'] : $patientData['password'];
//        $this->updatePassword($patientID, $password);
//        $this->completeTransOrFail();
//        return result(true, [
//            'id' => $patientID,
//            'expireTime' => $expireOtp - time(),
//        ], 200);
    }

    /**
     * @param array $patientData
     * @param array $attrs
     * @param string|null $id
     * @return array
     */
    public function checkValidate(array $patientData, array $attrs = [], string $id = null)
    {
        $validator = app()->validator()->validate($patientData, [
            'phone' => [
                "rules" => Validator::callback(function ($phone) use ($id) {
                    $isValid = Validator::allOf(
                        new Phone()
                    )->validate($phone);
                    if (!$isValid)
                        return false;

                    return !self::makeInstance()
                        ->filterNotType(PatientMapper::TYPE_COVID)
                        ->filterPhone($phone)
                        ->filterNotID($id)
                        ->isExists();
                }),
                "messages" => [
                    'callback' => 'Số điện thoại không hợp lệ hoặc đã tồn tại'
                ]
            ],
            'siteID' => Validator::allOf(
                new NotBlank()
            ),
            'password' => [
                "rules" => Validator::allOf(
                    new NotBlank(),
                    new Length(4, 12)
                ),
                "messages" => [
                    'callback' => 'Mật khẩu không hợp lệ'
                ]
            ]
        ]);
        if (!$validator->isValid()) {
            return result(false, $validator->getErrors());
        }
        $data = $validator->getValues();
        return result(true, $data, 200);
    }
}