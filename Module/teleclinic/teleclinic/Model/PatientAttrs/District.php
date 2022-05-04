<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Exceptions\ValidatorException;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\Common\ApiPatientDvhcMapper;
use VrZip\Exception;

class District extends AbstractPatientAttr
{

    protected $attrCode = 'district';
    protected $attrName = 'Quận/huyện';
    protected $attrValue = '';
    protected $dData = '';

    public function validate(array $input)
    {
        $districtCode = $input[$this->attrCode] ?? null;
        $provinceCode = $input['province'] ?? null;
        $rule = Validator::key($this->attrCode, Validator::allOf(
            Validator::callback(function () use ($districtCode, $provinceCode) {
                return ApiPatientDvhcMapper::makeInstance()->filterID($districtCode)->filterParentID($provinceCode)->filterLevel(ApiPatientDvhcMapper::LEVEL_DISTRICT)->isExists();
            })
        ))->setTemplate('Quận/huyện không hợp lệ');
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
                    'attrCode' => 'districtName',
                    'attrName' => 'Tên quận huyện',
                    'attrValue' => ApiPatientDvhcMapper::makeInstance()->filterID($this->attrValue)->getEntity()->name,
                ]
            ];
        return [];
    }
}