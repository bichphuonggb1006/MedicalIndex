<?php

namespace Teleclinic\Teleclinic\Model;

use Company\File\Model\FileMapper;
use Company\SQL\AnyMapper;
use Company\SQL\DB;
use Company\VrZip\Exception;
use Respect\Validation\Rules\Callback;
use Respect\Validation\Rules\Date;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Rules\Phone;
use Respect\Validation\Rules\StringType;
use Respect\Validation\Validator;
use Teleclinic\Teleclinic\Model\PatientAttrs\AddressDetail;
use Teleclinic\Teleclinic\Model\PatientAttrs\Country;
use Teleclinic\Teleclinic\Model\PatientAttrs\MaritalStatus;
use Teleclinic\Teleclinic\Model\PatientAttrs\Profession;

/**
 * @property string $id
 * @property string $patientCode
 * @property string $attrCode
 * @property string $attrValue
 * @property string $attrName
 * @property string $dData
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $deletedAt
 */
class PatientAttrMapper extends AbstractMapper
{

    public static $attrsKey = [
        'phone' => \Teleclinic\Teleclinic\Model\PatientAttrs\Phone::class,
        'email' => \Teleclinic\Teleclinic\Model\PatientAttrs\Email::class,
        'province' => \Teleclinic\Teleclinic\Model\PatientAttrs\Province::class,
        'provinceName' => null,
        'district' => \Teleclinic\Teleclinic\Model\PatientAttrs\District::class,
        'districtName' => null,
        'ward' => \Teleclinic\Teleclinic\Model\PatientAttrs\Ward::class,
        'wardName' => null,
        'addressDetail' => AddressDetail::class,
        'profession' => Profession::class,
        'maritalStatus' => MaritalStatus::class,
        'country' => Country::class,
        'countryName' => null,
    ];

    function tableName()
    {
        return 'teleclinic_patient_attrs';
    }

    function tableAlias()
    {
        return 't_p_a';
    }

    public function filterPatientCode($code)
    {
        $this->where('patientCode=?', __FUNCTION__)->setParamWhere($code, __FUNCTION__);
        return $this;
    }

    public function filterAttrCode($code)
    {
        $this->where('attrCode=?', __FUNCTION__)->setParamWhere($code, __FUNCTION__);
        return $this;
    }
}