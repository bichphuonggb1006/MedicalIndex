<?php

namespace Teleclinic\ApiPatient\Requests;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\Email;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;


class PatientLoginRequest extends AbstractRequest
{

    /**
     * @param $input
     * @return $this
     */
    public function validate($input)
    {
        $rule = Validator::key('username', Validator::allOf(
            Validator::callback(function ($username) {
                if (!Validator::email()->validate($username) && !Validator::phone()->validate($username))
                    return false;
                //Check username is exists
                if (!ApiPatientPatientAccountMapper::makeInstance()->filterUsername($username)->isExists())
                    return false;
                return true;
            })
        ))
            ->key('password', Validator::allOf(
                Validator::notBlank(),
                Validator::length(6,12)
            ));
        parent::assert($rule, $input);
        return $this;
    }


}