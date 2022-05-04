<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;

class Profession extends AbstractPatientAttr
{

    protected $attrCode = 'profession';
    protected $attrName = 'Nghề nghiệp';


    public function validate(array $input)
    {
        $rule = Validator::key($this->attrCode, Validator::nullable(
            Validator::stringType(),
            Validator::length(0, 255)
        ))->setName($this->attrName);
        $this->assert($rule, $input);
    }


}