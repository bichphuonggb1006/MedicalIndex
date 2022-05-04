<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Validator;
use Teleclinic\Teleclinic\Model\CountryMapper;

class Country extends AbstractPatientAttr
{

    protected $attrCode = 'country';
    protected $attrName = 'Quốc tịch';
    protected $attrValue = '';
    protected $dData = '';

    public function validate(array $input)
    {
        $country = $input[$this->attrCode] ?? null;
        $rule = Validator::key($this->attrCode, Validator::allOf(
            Validator::callback(function () use ($country) {
                return CountryMapper::makeInstance()->filterID($country)->isExists();
            })
        ))->setTemplate('Quốc tịch không hợp lệ');
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
                    'attrCode' => 'countryName',
                    'attrName' => $this->attrName,
                    'attrValue' => CountryMapper::makeInstance()->filterID($this->attrValue)->getEntity()->name_en,
                ]
            ];
        return [];
    }
}