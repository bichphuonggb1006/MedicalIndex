<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;

class MaritalStatus extends AbstractPatientAttr
{

    const MARITAL_STATUS_MARRIED = 'married';
    const MARITAL_STATUS_UNMARRIED = 'unmarried';

    public static $allMarital = [
        self::MARITAL_STATUS_UNMARRIED => 'Chưa kết hôn',
        self::MARITAL_STATUS_MARRIED => 'Đã kết hôn',
    ];

    protected $attrCode = 'maritalStatus';
    protected $attrName = 'Tình trạng hôn nhân';


    public function validate(array $input)
    {
        $rule = Validator::key($this->attrCode, Validator::nullable(
            Validator::in(array_keys(self::$allMarital))
        ))->setName($this->attrName);
        $this->assert($rule, $input);
    }


}