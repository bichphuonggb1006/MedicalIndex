<?php

namespace Teleclinic\Teleclinic\Model;

use Company\ServiceNotification\ServiceNotify;

class OtpMapper extends AbstractMapper
{
    const TYPE_PATIENT_REGISTER = 'PATIENT_REGISTER';
    const TYPE_PATIENT_FORGOT_PASSWORD = 'PATIENT_FORGOT_PASSWORD';

    public static function allType(){
        return [
            self::TYPE_PATIENT_REGISTER=>self::TYPE_PATIENT_REGISTER,
            self::TYPE_PATIENT_FORGOT_PASSWORD=>self::TYPE_PATIENT_FORGOT_PASSWORD,
        ];
    }


    const STATUS_NEW = 1;
    const STATUS_ACTIVATED = 2;

    public function tableAlias()
    {
        return 'totp';
    }

    public function tableName()
    {
        return 'teleclinic_otp';
    }

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $n
     * @return string
     */
    function generateNumericOTP($n)
    {
        // Take a generator string which consist of
        // all numeric digits
        $generator = "1357902468";

        // Iterate for n-times and pick a single character
        // from generator and append it to $result

        // Login for generating a random character from generator
        //     ---generate a random number
        //     ---take modulus of same with length of generator (say i)
        //     ---append the character at place (i) from generator to result
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        // Return result
        return $result;
    }


    public function store($phone, $type, $referenceID, $expireTime, $autoSend = true)
    {
        $otp = $this->generateNumericOTP(6);
        if (app()->config['production'] == 0) {
            $otp = 123456;
        }
        $id = uid();
        $otpData = [
            'id' => $id,
            'type' => $type,
            'otp' => $otp,
            'status' => self::STATUS_NEW,
            'referenceID' => $referenceID,
            'expireTime' => date('Y-m-d H:i:s', $expireTime)
        ];
        $content = json_encode([
            'otp' => $otp,
            'to' => [$phone]
        ]);
        OtpMapper::makeInstance()->insert($otpData);
        $rsPush = (new ServiceNotify())->sendSMSPatientActiveAccount($id, $content, [$phone]);
        OtpMapper::makeInstance()->filterID($id)->update([
            'response' => $rsPush->toJson()
        ]);
        return result(true, [
            'otp' => $otp
        ], 200);
    }


    public function filterOtp($otp)
    {
        $this->where("otp=?", __FUNCTION__)
            ->setParamWhere($otp, __FUNCTION__);
        return $this;
    }

    public function filterStatus($status)
    {
        $this->where("status=?", __FUNCTION__)
            ->setParamWhere($status, __FUNCTION__);
        return $this;
    }

    public function filterType($type)
    {
        $this->where("type=?", __FUNCTION__)
            ->setParamWhere($type, __FUNCTION__);
        return $this;
    }

    /**
     * @param $term string Format Y-m-d
     * @return $this
     */
    public function filterCreatedAt($term)
    {
        $this->where("DATE(type)=?", __FUNCTION__)
            ->setParamWhere($term, __FUNCTION__);
        return $this;
    }

    public function filterReferenceID($referenceID)
    {
        $this->where("referenceID=?", __FUNCTION__)
            ->setParamWhere($referenceID, __FUNCTION__);
        return $this;
    }

    /**
     * @param $datetime string Y-m-d H:i:S
     * @return $this
     */
    public function filterUnexpired($datetime)
    {
        $this->where("expireTime >= ?", __FUNCTION__)
            ->setParamWhere($datetime, __FUNCTION__);
        return $this;
    }
}