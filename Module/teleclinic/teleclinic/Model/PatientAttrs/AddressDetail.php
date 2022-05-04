<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;

class AddressDetail extends AbstractPatientAttr
{

    protected $attrCode = 'addressDetail';
    protected $attrName = 'Số nhà/thôn xóm';
    protected $attrValue = '';

    public function validate(array $input)
    {
        $rule = Validator::key($this->attrCode, Validator::allOf(
            Validator::stringType(),
            Validator::length(0, 255)
        ))->setName($this->attrName);
        $this->assert($rule, $input);
    }




}