<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;

class Phone extends AbstractPatientAttr
{

    protected $attrCode = 'phone';
    protected $attrName = 'Số điện thoại';
    protected $attrValue = '';

    public function validate(array $input)
    {
        $phone = $input[$this->attrCode] ?? null;
        $rule = Validator::key($this->attrCode, Validator::nullable(
            Validator::phone()
        ))->setName($this->attrName);
        $this->assert($rule, $input);
    }




}