<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;

class Email extends AbstractPatientAttr
{

    protected $attrCode = 'email';
    protected $attrName = 'Địa chỉ email';


    public function validate(array $input)
    {
        $email = $input[$this->attrCode] ?? null;
        $rule = Validator::key($this->attrCode, Validator::nullable(
            Validator::email()
        ))->setName($this->attrName);
        $this->assert($rule, $input);
    }


}