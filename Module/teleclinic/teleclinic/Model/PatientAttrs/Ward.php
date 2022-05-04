<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\Common\ApiPatientDvhcMapper;

class Ward extends AbstractPatientAttr
{

    protected $attrCode = 'ward';
    protected $attrName = 'Phường/xã';
    protected $attrValue = '';

    public function validate(array $input)
    {
        $ward = $input[$this->attrCode] ?? null;
        $districtCode = $input['district'] ?? null;
        $rule = Validator::key($this->attrCode, Validator::allOf(
            Validator::callback(function () use ($ward,$districtCode) {
                return ApiPatientDvhcMapper::makeInstance()->filterID($ward)
                    ->filterParentID($districtCode)
                    ->filterLevel(ApiPatientDvhcMapper::LEVEL_WARD)
                    ->isExists();
            })
        ))->setTemplate('Phường/xã không hợp lệ');
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
                    'attrCode' => 'wardName',
                    'attrName' => 'Tên phường/xã',
                    'attrValue' => ApiPatientDvhcMapper::makeInstance()->filterID($this->attrValue)->getEntity()->name,
                ]
            ];
        return [];
    }

}