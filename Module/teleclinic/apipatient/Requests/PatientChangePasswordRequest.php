<?php

namespace Teleclinic\ApiPatient\Requests;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\Email;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;


class PatientChangePasswordRequest extends AbstractRequest
{
    /**
     * @var string $patientCode
     */
    protected $patientCode;

    public function __construct($patientCode)
    {
        $this->patientCode = $patientCode;
    }

    /**
     * @param $input
     * @return $this
     */
    public function validate($input)
    {
        $rules = Validator::key('password_old', Validator::allOf(
            Validator::callback(function ($password_od) {
                if (!ApiPatientPatientAccountMapper::makeInstance()->filterPatientCode($this->patientCode)->filterPassword($password_od)->isExists())
                    return false;
                return true;
            })->setTemplate('Mật khẩu hiện tại không hợp lệ')
        ))
            ->key('password', Validator::allOf(
                Validator::length(4, 12)->setTemplate('Mật khẩu phải có độ dài từ 4 đến 12 ký tự'),
                Validator::equals($input['password_confirmation'] ?? null)->setTemplate('Mật khẩu xác thực không hợp lệ')
            ));
        parent::assert($rules, $input);
        return $this;
    }


}