<?php

namespace Teleclinic\ApiPatient\Controller\Common;

use Company\MVC\Bootstrap;
use Company\MVC\Controller;
use Firebase\JWT\JWT;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;
use Teleclinic\ApiPatient\Model\ApiPatientPatientMapper;
use Teleclinic\Teleclinic\Model\OtpMapper;

class OtpController extends Controller
{

    public function getOpt()
    {
        $rs = new \Result();
        $phone = $this->input('phone');
        if (!Validator::phone()->validate($phone)) {
            $rs->setErrors('Dữ liệu không hợp lệ', [], 422);
            return $this->outputJSON($rs);
        }
        /**
         * @var ApiPatientPatientAccountMapper $account
         */
        $account = ApiPatientPatientAccountMapper::makeInstance()->filterUsername($phone)->getEntity();
        if ($account->status !== ApiPatientPatientAccountMapper::STATUS_NEW) {
            $rs->setErrors('Tài khoản đã được kích hoạt hoặc bị cấm truy cập', [], 403);
            return $this->outputJSON($rs);
        }
        //Otp cũ chưa hết hạn => Không gửi lại
        $accountCreatedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $account->createdAt)->getTimestamp();
        if ($accountCreatedAt + 15 * 60 <= time()) {
            //Chỉ hỗ trợ gửi otp khi tài khoản đăng ký trong vòng 15 phút
            $rs->setErrors('Yêu cầu không hợp lệ');
            return $this->outputJSON($rs);
        }
        $otpEntity = OtpMapper::makeInstance()
            ->filterType(OtpMapper::TYPE_PATIENT_REGISTER)
            ->filterReferenceID($phone)
            ->filterStatus(OtpMapper::STATUS_NEW)
            ->filterUnexpired(date('Y-m-d H:i:s'))
            ->orderBy('createdAt desc')
            ->getEntity();
        if ($otpEntity->id) {
            //Otp cũ chưa hết hạn => Không gửi lại
            $expireTime = \DateTime::createFromFormat('Y-m-d H:i:s', $otpEntity->expireTime)->getTimestamp();
            $rs->setSuccess([
                'expireTime' => $expireTime - time(),
                'message' => 'Mã Otp đã gửi thành công'
            ]);

            return $this->outputJSON($rs);
        }
        //Todo chặn nếu gửi quá nhiều lần/ngày
        OtpMapper::makeInstance()
            ->filterType(OtpMapper::TYPE_PATIENT_REGISTER)
            ->filterCreatedAt(date('Y-m-d'))
            ->filterReferenceID($phone)->count($countSendOtp);
        if ($countSendOtp > 5) {
            $rs->setErrors('Bạn đã gửi otp quá 5 lần, vui lòng liên hệ bộ phận chăm sóc khách hàng để được hỗ trợ!');
            return $this->outputJSON($rs);
        }
        $expireTime = app()->config['otp']['expire_time_verify_otp_active_patient'];
        OtpMapper::makeInstance()->store($phone, OtpMapper::TYPE_PATIENT_REGISTER, $phone, $expireTime);
        $rs->setSuccess([
            'expireTime' => $expireTime - time(),
            'message' => 'Mã Otp đã gửi thành công'
        ]);
        return $this->outputJSON($rs);
    }

    public function verifyOtp()
    {
        $rs = new \Result();
        $phone = $this->input('phone');
        $type = $this->input('type');
        $otp = $this->input('otp', '');
        if (!Validator::notBlank()->validate($otp) || !Validator::phone()->validate($phone) || !Validator::in(array_keys(OtpMapper::allType()))->validate($type)) {
            $rs->setErrors('Dữ liệu không hợp lệ', [], 422);
            return $this->outputJSON($rs);
        }
        $otpEntity = OtpMapper::makeInstance()
            ->filterType($type)
            ->filterReferenceID($phone)
            ->filterOtp($otp)
            ->filterStatus(OtpMapper::STATUS_NEW)
            ->filterUnexpired(date('Y-m-d H:i:s'))
            ->getEntity();
        if (!$otpEntity->id) {
            $rs->setErrors('Mã xác thực không hợp lệ', [], 422);
            return $this->outputJSON($rs);
        }
        /**
         * @var ApiPatientPatientAccountMapper $account
         */
        $account = ApiPatientPatientAccountMapper::makeInstance()->filterUsername($phone)->getEntity();
        if ($type == OtpMapper::TYPE_PATIENT_REGISTER) {
            $rs = $this->checkValidActiveAccount($account);
        } else {
            $rs = $this->setTokenResetPassword($account);
        }
        if (!$rs->isOk()) {
            return $this->outputJSON($rs);
        }
        OtpMapper::makeInstance()->filterID($otpEntity->id)->update([
            'status' => OtpMapper::STATUS_ACTIVATED
        ]);
        return $this->outputJSON($rs);
    }


    private function checkValidActiveAccount($account)
    {
        $rs = new \Result();
        if ($account->status !== ApiPatientPatientAccountMapper::STATUS_NEW) {
            $rs->setErrors('Tài khoản đã được kích hoạt hoặc bị cấm truy cập', [], 403);
            return $this->outputJSON($rs);
        }
        ApiPatientPatientAccountMapper::makeInstance()->filterUsername($account->username)->update([
            'status' => ApiPatientPatientAccountMapper::STATUS_ACTIVE,
            'verifyAt' => date('Y-m-d H:i:s')
        ]);
        return $rs->setSuccess();
    }

    public function setTokenResetPassword($account): \Result
    {
        return ApiPatientPatientAccountMapper::makeInstance()->setTokenResetPassword($account->username);
    }
}