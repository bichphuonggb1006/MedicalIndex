<?php

namespace Teleclinic\ApiPatient\Controller;

use Company\Exception\UnauthorizedException;
use Company\MVC\Bootstrap;
use Company\MVC\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Respect\Validation\Rules\AllOf;
use Respect\Validation\Rules\Equals;
use Respect\Validation\Rules\Length;
use Respect\Validation\Rules\NotBlank;
use Respect\Validation\Rules\NotEmpty;
use Respect\Validation\Rules\NoWhitespace;
use Respect\Validation\Rules\Phone;
use Respect\Validation\Validator;
use Teleclinic\ApiPatient\Auth\Auth;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;
use Teleclinic\ApiPatient\Repositories\ApiPatientPatientRepository;
use Teleclinic\ApiPatient\Requests\PatientChangePasswordRequest;
use Teleclinic\ApiPatient\Requests\PatientLoginRequest;
use Teleclinic\ApiPatient\Requests\PatientRegisterRequest;
use Teleclinic\ApiPatient\Model\ApiPatientPatientMapper;
use Teleclinic\Teleclinic\Model\OtpMapper;
use Teleclinic\Teleclinic\Model\PatientAccount;
use Teleclinic\Teleclinic\Model\ScheduleMapper;


class PatientController extends Controller
{
    /**
     * @return void
     * @throws \Company\Exception\NotFoundException
     * @throws \Company\Exception\UnauthorizedException
     */
    public function login()
    {
        $response = new \Result();
        $validator = (new PatientLoginRequest())->validate($this->input());
        if (!$validator->isValid()) {
            $response->setErrors('Thông tin đăng nhập không hợp lệ', [], 422);
            return $this->outputJSON($response->toArray());
        }
        $dataLogin = $validator->getInputs();
        $expireTime = time() + app()->config['jwtPatient']['exp'];
        /**
         * @var \Result $respLogin
         */
        $respLogin = Auth::login($dataLogin['username'], $dataLogin['password'], $expireTime);
        return $this->outputJSON($respLogin->toArray());
    }

    /**
     * @return void
     */
    public function logOut()
    {
        //TODO:: Đưa token vào blacklist... Chưa xử lý
        //Auth::makeInstance()->logout();
        return $this->outputJSON([]);
    }

    /**
     * @return void
     * @throws UnauthorizedException
     */
    public function getProfile()
    {
        $response = new \Result();
        $patient = Auth::makeInstance()->requireLogin()->user();
        $response->setSuccess($patient);
        return $this->outputJSON($response->toArray());
    }

    /**
     * @return void
     * @throws UnauthorizedException
     * @throws \Company\Exception\NotFoundException
     * @throws \Respect\Validation\Exceptions\ComponentException
     */
    public function changePassword()
    {
        /**
         * @var ApiPatientPatientMapper $patientEntity
         */
        $patientEntity = Auth::makeInstance()->requireLogin()->user();

        $response = new \Result();
        $validator = (new PatientChangePasswordRequest($patientEntity->code))->validate($this->input());
        if (!$validator->isValid()) {
            $response->setErrors('Dữ liệu không hợp lệ', $validator->getMessages(), 422);
            return $this->outputJSON($response->toArray());
        }
        $dataChangePassword = $validator->getInputs();
        Auth::makeInstance()->changePassword($patientEntity->code, $dataChangePassword['password']);
        $response->setSuccess();
        return $this->outputJSON($response->toArray());
    }


    public function postNewPassword()
    {
        $rs = new \Result();
        $token = $this->input('token');
        $phone = $this->input('phone');
        $password = $this->input('password');
        $passwordConfirmation = $this->input('password_confirmation');
        try {
            (new AllOf(
                new NotBlank(),
                new NotEmpty(),
                new Length(4, 12),
                new NoWhitespace(),
                new Equals($passwordConfirmation)
            ))->check($password);
            Validator::phone()->check($phone);
            Validator::notBlank()->check(token);
        } catch (\Exception $exception) {
            $rs->setErrors('Param is invalid', [
                'password' => $exception->getMessage()
            ], 422);
            return $this->outputJSON($rs);
        }
        /**
         * @var ApiPatientPatientAccountMapper $account
         */
        $account = ApiPatientPatientAccountMapper::makeInstance()->filterUsername($phone)->filterTokenResetPassword($token)->getEntity();
        if (!$account->id) {
            $rs->setErrors('Param is invalid', [], 422);
            return $this->outputJSON($rs);
        }
        ApiPatientPatientAccountMapper::makeInstance()->updatePassword($account->patientCode, $password);
        ApiPatientPatientAccountMapper::makeInstance()->filterID($account->id)->update(
            ['token_reset_password' => null]
        );
        $rs->setSuccess();
        return $this->outputJSON($rs);

    }


    /**
     * @return void
     */
    public function checkPhoneBeforeRegister()
    {
        $phone = $this->input('username');
        (new AllOf(
            new Phone(),
            new NotEmpty(),
            new NotBlank()
        ))->check($phone);
        $phoneExists = ApiPatientPatientMapper::makeInstance()
            ->filterNotType(ApiPatientPatientMapper::TYPE_COVID)
            ->filterPhone($phone)
            ->isExists();
        return $this->outputJSON([
            'exists' => $phoneExists
        ]);
    }


    /**
     * Đăng ký tài khoản
     */
    public function register()
    {
        $response = new \Result();
        $validator = (new PatientRegisterRequest())->validate($this->input());
        if (!$validator->isValid()) {
            $response->setErrors('Param is invalid', $validator->getMessages(), 422);
            return $this->outputJSON($response->toArray());
        }
        $registerData = $validator->getInputs();
        $rsCreateAccount = ApiPatientPatientAccountMapper::makeInstance()->createAccount($registerData['username'], $registerData['password']);
        $this->outputJSON($rsCreateAccount->toArray());

    }

    public function verifyOtp($siteID)
    {
        $siteID = 'master';
        $patietnID = $this->input('id');
        $otp = $this->input('otp');
        $type = $this->input('type');
        if ($type == OtpMapper::TYPE_PATIENT_REGISTER) {
            $patientEntity = ApiPatientPatientMapper::makeInstance()
                ->filterNotType(ApiPatientPatientMapper::TYPE_COVID)
                ->filterStatus(ApiPatientPatientMapper::STATUS_NEW)
                ->filterSiteID($siteID)->filterID($patietnID)->getEntity();
            $otpEntity = OtpMapper::makeInstance()
                ->filterUnexpired(date('Y-m-d H:i:s'))
                ->filterOtp($otp)
                ->filterType(OtpMapper::TYPE_PATIENT_REGISTER)
                ->filterStatus(OtpMapper::STATUS_NEW)
                ->filterReferenceID($patietnID)
                ->getEntity();

            if ($patientEntity->id && $otpEntity->id) {
                OtpMapper::makeInstance()->filterID($otpEntity->id)->update([
                    'status' => OtpMapper::STATUS_ACTIVATED,
                ]);
                ApiPatientPatientMapper::makeInstance()->filterID($patientEntity->id)->update([
                    'status' => ApiPatientPatientMapper::STATUS_ACTIVATED
                ]);

                return $this->outputJSON(result(true, [], 200));
            }
        } else {
            $patientEntity = ApiPatientPatientMapper::makeInstance()
                ->filterNotType(ApiPatientPatientMapper::TYPE_COVID)
                ->filterStatus(ApiPatientPatientMapper::STATUS_ACTIVATED)
                ->filterSiteID($siteID)
                ->filterID($patietnID)
                ->getEntity();

            $otpEntity = OtpMapper::makeInstance()
                ->filterUnexpired(date('Y-m-d H:i:s'))
                ->filterOtp($otp)
                ->filterType(OtpMapper::TYPE_PATIENT_FORGOT_PASSWORD)
                ->filterStatus(OtpMapper::STATUS_NEW)
                ->filterReferenceID($patietnID)
                ->getEntity();

            if ($patientEntity->id && $otpEntity->id) {
                $tokenResetPassword = md5($patietnID->id . time());
                ApiPatientPatientMapper::makeInstance()->filterID($patientEntity->id)->update([
                    'tokenResetPassword' => $tokenResetPassword
                ]);
                return $this->outputJSON(result(true, ['tokenResetPassword' => $tokenResetPassword], 200));
            }
        }

        return $this->outputJSON(result(false, [], 500));
    }

    /**
     * @return void
     */
    public function forgotPassword()
    {
        $rs = new \Result();
        //Email or Password
        $username = $this->input('username');
        $account = ApiPatientPatientAccountMapper::makeInstance()->filterUsername($username)->getEntity();
        if (!$account->id) {
            $rs->setErrors('Thông tin tài khoản không hợp lệ');
            return $this->outputJSON($rs);
        }
        if ($account->status == ApiPatientPatientAccountMapper::STATUS_BLOCKED) {
            $rs->setErrors('Tài khoản bị cấm truy cập', [], 403);
            return $this->outputJSON($rs);
        }

        if (Validator::phone()->validate($username)) {
            //Phone
            $rs = $this->forgotPasswordByPhone($account);
        } else {
            //Email
            $rs = $this->forgotPasswordByEmail($account);
        }
        return $this->outputJSON($rs);
    }


    /**
     * @param $account
     * @return \Result|null
     */
    private function forgotPasswordByEmail($account): ?\Result
    {
        //Todo Gửi link đổi mật khẩu vào email kèm token xác thực để tạo mật khẩu mới
        $rs = new \Result();



        $rs->setSuccess([
            'message' => 'Gửi thông tin khôi mục mật khẩu thành công! Bạn vui lòng kiểm tra hòm thư và thực hiện theo hướng dẫn.'
        ]);
        return $rs;

    }

    /**
     * Gửi otp xác thực tài khoản
     * @param $account
     * @param $account
     * @return \Result|null
     */
    private function forgotPasswordByPhone($account): ?\Result
    {
        $rs = new \Result();
        //Todo chặn nếu gửi quá nhiều lần/ngày
        OtpMapper::makeInstance()
            ->filterType(OtpMapper::TYPE_PATIENT_FORGOT_PASSWORD)
            ->filterCreatedAt(date('Y-m-d'))
            ->filterReferenceID($account->username)->count($countSendOtp);
        if ($countSendOtp > 5) {
            $rs->setErrors('Bạn đã gửi otp quá 5 lần, vui lòng liên hệ bộ phận chăm sóc khách hàng để được hỗ trợ!');
            return $this->outputJSON($rs);
        }
        $otpEntity = OtpMapper::makeInstance()
            ->filterType(OtpMapper::TYPE_PATIENT_FORGOT_PASSWORD)
            ->filterReferenceID($account->username)
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
        $expireTime = app()->config['otp']['expire_time_verify_otp_active_patient'];
        OtpMapper::makeInstance()->store($account->username, OtpMapper::TYPE_PATIENT_FORGOT_PASSWORD, $account->username, $expireTime);
        $rs->setSuccess([
            'expireTime' => $expireTime - time(),
            'message' => 'Mã Otp đã gửi thành công'
        ]);
        return $rs;
    }

    public function postProfile()
    {
        /**
         * @var ApiPatientPatientMapper $patientEntity
         */
        $patientEntity = Auth::makeInstance()->requireLogin()->user();
        $rsUpdatePatient = (new ApiPatientPatientRepository())->update($patientEntity, $this->input());
        return $this->outputJSON($rsUpdatePatient->toArray());
    }
}