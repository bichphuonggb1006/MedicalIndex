<?php

namespace Teleclinic\ApiPatient\Requests;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\Email;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;


class PatientRegisterRequest extends AbstractRequest
{

    /**
     * @param $input
     * @return $this
     */
    public function validate($input)
    {
        $rules = Validator::key('username', Validator::allOf(
            Validator::callback(function($username){
                if(!Validator::email()->validate($username) && !Validator::phone()->validate($username))
                    return false;
                //Check username is exists
                if(ApiPatientPatientAccountMapper::makeInstance()->filterUsername($username)->isExists())
                    return false;
                return true;
            })
        ))
            ->key('password', Validator::allOf(
                Validator::length(4, 12)->setTemplate('Mật khẩu phải có độ dài từ 4 đến 12 ký tự'),
                Validator::equals($input['password_confirmation'] ?? null)->setTemplate('Mật khẩu xác thực không hợp lệ')
            ));
        parent::assert($rules, $input);
        return $this;
    }


}