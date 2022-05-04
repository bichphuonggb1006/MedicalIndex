<?php

namespace Teleclinic\ApiPatient\Requests;

use Respect\Validation\Rules\ArrayType;
use Respect\Validation\Validator;
use Respect\Validation\Rules\Callback;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Rules\StringType;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;
use Teleclinic\Teleclinic\Model\PatientMapper;

class PatientUpdateRequest extends AbstractRequest
{
    /**
     * @var
     */
    private $patientEntityCurrent;

    public function __construct($patientEntityCurrent)
    {
        $this->patientEntityCurrent = $patientEntityCurrent;
    }

    public function validate(array $input)
    {
        $rules = Validator::key('fullName', Validator::allOf(
            Validator::notBlank(),
            Validator::length(3, 255),
            Validator::stringType()
        ))
            ->key('birthDate', Validator::allOf(
                Validator::date('Y-m-d')
            ))
            ->key('gender', Validator::in(['F', 'M', 'O']))
            ->key('birthDate',Validator::date('Y-m-d'))
            ->key('cardDate',Validator::nullable(Validator::date('Y-m-d')))
            ->key('phone', Validator::callback(function ($phone) use (&$input) {
                //Nếu số điện thoại đã tồn tại => Bỏ qua ko xử lý vì không được phép thay đổi
                if ($this->patientEntityCurrent->phone) {
                    if (isset($input['phone']))
                        unset($input['phone']);
                    return true;
                }

                if (trim($phone)) {
                    if (!Validator::phone()->validate($phone))
                        return false;
                    //Nếu trùng với user khác thì báo lỗi
                    if (ApiPatientPatientAccountMapper::makeInstance()->filterUsername($phone)->isExists()) {
                        return false;
                    }
                }
                return true;
            }));;
        parent::assert($rules, $input);
        return $this;
    }
}