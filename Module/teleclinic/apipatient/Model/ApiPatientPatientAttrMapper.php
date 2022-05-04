<?php

namespace Teleclinic\ApiPatient\Model;

use Company\SQL\Mapper;
use Respect\Validation\Rules\Callback;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Rules\Phone;
use Respect\Validation\Rules\StringType;
use Respect\Validation\Validator;
use Teleclinic\Teleclinic\Model\AbstractMapper;
use Teleclinic\Teleclinic\Model\OtpMapper;

/**
 * @property int $id
 * @property int $code
 * @property string $cardID
 * @property string $birthDate
 * @property string $fullName
 * @property string $gender
 */
class ApiPatientPatientAttrMapper extends \Teleclinic\Teleclinic\Model\PatientAttrMapper
{

}