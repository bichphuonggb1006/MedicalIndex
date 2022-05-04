<?php

namespace Teleclinic\ApiDoctor\Auth;


use Company\Entity\Entity;
use Company\Exception\NotFoundException;
use Company\Exception\UnauthorizedException;
use Company\MVC\Bootstrap;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Respect\Validation\Exceptions\ValidationException;
use Teleclinic\ApiDoctor\Model\ApiDoctorUserLoginMapper;

class Auth extends \Slim\Middleware
{
    /**
     * @var Entity
     */
    private static $doctor;
    private static $instance;

    /**
     * @param $username
     * @param $password
     * @param $expt
     * @return array
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public static function login($username, $password, $expt)
    {
        $doctor = ApiDoctorUserLoginMapper::makeInstance()->login($username, $password, $expt);
        if (!$doctor->id) {
            throw new UnauthorizedException('Unauthorized', '401');
        }
        self::$doctor = $doctor;
        $acceptToken = self::makeInstance()->generateToken($expt);
        return result(true, [
            'docter' => $doctor,
            'acceptToken' => $acceptToken,
            'expireTime' => $expt,
            'acceptTokenType' => 'Bearer'
        ],200);
    }

    /**
     * @param $exp
     * @param $doctor
     * @return string
     * @throws NotFoundException
     */
    public function generateToken($exp, $doctor = null): string
    {
        if (is_null($doctor))
            $doctor = self::$doctor;

        if (!$doctor->id)
            throw new NotFoundException('Patient is not found!', 404);

        $payload = array(
            "iss" => Bootstrap::getInstance()->config['jwtDoctor']['iss'],
            "aud" => Bootstrap::getInstance()->config['jwtDoctor']['aud'],
            "iat" => time(),
            "exp" => $exp,
            "nbf" => time(),
            "patient" => $doctor->toJson(),
            "patient_id" => $doctor->id,
            "updatedAt" => $doctor->updatedAt,
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        return JWT::encode($payload, Bootstrap::getInstance()->config['jwtPatient']['secret'], 'HS256');
    }

    public function logout()
    {
        self::$doctor = null;
    }

    public static function makeInstance()
    {
        if (!self::$instance)
            self::$instance = new Auth();
        return self::$instance;
    }

    /**
     * @return $this
     * @throws UnauthorizedException
     */
    public function requireLogin(): self
    {
        $authorization = Bootstrap::getInstance()->slim->request()->headers('Authorization');
        if (!empty($authorization)) {
            if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
                $token = $matches[1];
            }
        }
        if (!isset($token))
            throw new UnauthorizedException('Unauthorized', '401');

        self::$doctor = $this->verifyToken($token);
        if (!self::$doctor)
            throw new UnauthorizedException('Unauthorized', '401');
        return $this;
    }

    /**
     * @param string $token
     * @return Entity|null
     */
    public function verifyToken(string $token): ?Entity
    {
        if (trim($token) == '')
            return false;
        $decoded = JWT::decode($token, new Key(Bootstrap::getInstance()->config['jwtPatient']['secret'], 'HS256'));
        $patient = ApiDoctorUserLoginMapper::makeInstance()
            ->filterID($decoded->patient_id)->getEntity();
        unset($patient->patientPassword);
        return $patient->id && $patient->status != ApiDoctorUserLoginMapper::STATUS_BAN && !is_null($patient->updatedAt) ? $patient : null;
    }

    public function user()
    {
        return self::$doctor;
    }

    /**
     * @param Entity $patientEntity
     * @param $passwordOld
     * @param $passwordNew
     * @return void
     */
    public function changePassword(Entity $patientEntity, $passwordOld, $passwordNew)
    {
        $patient = ApiDoctorUserLoginMapper::makeInstance()
            ->filterPhone($patientEntity->phone)->filterPassword(md5($passwordOld))->getEntity();
        if (!$patient->id) {
            throw new ValidationException('Password old is invalid', 422);
        }

        $isUpdated = ApiDoctorUserLoginMapper::makeInstance()
            ->filterPhone($patientEntity->phone)->filterPassword(md5($passwordOld))->update([
                'password' => md5($passwordNew)
            ]);

        //TODO xử lý nếu update mật khẩu cũ giống mật khẩu mới sẽ bị failed
        if (!$isUpdated)
            throw new \Exception();
    }

    public function call()
    {
        // TODO: Implement call() method.
    }
}