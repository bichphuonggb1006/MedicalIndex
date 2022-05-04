<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\Common\ApiPatientDvhcMapper;

class Province extends AbstractPatientAttr
{

    protected $attrCode = 'province';
    protected $attrName = 'Tỉnh/Thành phố';
    protected $attrValue = '';

    public function validate(array $input)
    {
        $province = $input[$this->attrCode] ?? null;
        $rule = Validator::key($this->attrCode,Validator::allOf(
            Validator::callback(function ()use($province) {
                return ApiPatientDvhcMapper::makeInstance()->filterID($province)->filterLevel(ApiPatientDvhcMapper::LEVEL_PROVINCE)->isExists();
            })
        ))->setTemplate('Tỉnh/Thành phố không hợp lệ');
        $this->assert($rule, $input);
    }

    public function toArray(): array
    {
        if ($this->valid)
            return [
                [
                    'attrCode' => $this->attrCode,
                    'attrName' => $this->attrName,
                    'attrValue' => $this->attrValue,
                ],
                [
                    'attrCode' => 'provinceName',
                    'attrName' => 'Tên tỉnh thành/phố',
                    'attrValue' => ApiPatientDvhcMapper::makeInstance()->filterID($this->attrValue)->getEntity()->name,
                ]
            ];
        return [];
    }
}