<?php

namespace Teleclinic\ApiPatient\Model;

use Company\Entity\Entity;
use Company\MVC\Trigger;
use Teleclinic\Teleclinic\Model\OtpMapper;
use Company\ServiceNotification\ServiceNotify;
use Respect\Validation\Validator;
use Slim\Log;


class ApiPatientPatientAccountMapper extends \Teleclinic\Teleclinic\Model\PatientAccount
{
    /**
     * @param $username
     * @param $password
     * @return \Result
     */
    public function login($username, $password): \Result
    {
        $response = new \Result();
        /**
         * @var ApiPatientPatientAccountMapper $account
         */
        $account = ApiPatientPatientAccountMapper::makeInstance()
            ->filterUsername($username)
            ->filterPassword($password)
            ->getEntity();
        if (!$account->id)
            return $response->setErrors('Thông tin đăng nhập không hợp lệ', [], 404);

        if ($account->status == ApiPatientPatientAccountMapper::STATUS_BLOCKED)
            return $response->setErrors('Tài khoản bị cấm truy cập', [], 403);

        $patient = ApiPatientPatientMapper::makeInstance()->filterCode($account->patientCode)->orderBy('createdAt desc')->getEntity();
        if (!$patient->id)
            return $response->setErrors('', [], 500);

        return $response->setSuccess(['patient' => $patient, 'patientAccount' => $account]);
    }


    /**
     * @param $username
     * @param $password
     * @return void
     */
    public function createAccount($username, $password): \Result
    {
        $response = new \Result();
        //Tạo patient
        $patientCode = uid();
        $patientID = uid();
        $this->startTrans();
        ApiPatientPatientMapper::makeInstance()->insert([
            'code' => $patientCode,
            'id' => $patientID
        ]);
        $createdAt = \date('Y-m-d H:i:s');
        ApiPatientPatientAccountMapper::makeInstance()->insert([
            'id' => uid(),
            'patientCode' => $patientCode,
            'status' => Validator::phone()->validate($username) ? ApiPatientPatientAccountMapper::STATUS_NEW : ApiPatientPatientAccountMapper::STATUS_ACTIVE,
            'password' => md5($password),
            'username' => $username,
            'createdAt' => $createdAt
        ]);
        $this->completeTransOrFail();
        if (Validator::phone()->validate($username)) {
            $expireTime = app()->config['otp']['expire_time_verify_otp_active_patient'];
            OtpMapper::makeInstance()->store($username, OtpMapper::TYPE_PATIENT_REGISTER, $username, $expireTime);
            $response->setSuccess([
                'expireTime' => $expireTime - time(),
                'message' => 'Mã Otp đã gửi thành công'
            ]);
        } else {
            //Crate link active account
            //Todo push service send email
            $body = [
                'patientCode' => $patientCode,
                'email' => $username,
                'createdAt' => $createdAt
            ];
            (new ServiceNotify())->sendEmailPatientActiveAccount($username, json_encode($body), [$username]);
            $response->setSuccess();
        }
        return $response;
    }

    public function setTokenResetPassword($username)
    {
        $token = uid();
        self::makeInstance()->filterUsername($username)->update([
            'token_reset_password' => $token,
        ]);
        return (new \Result())->setSuccess(['token' => $token]);
    }


}