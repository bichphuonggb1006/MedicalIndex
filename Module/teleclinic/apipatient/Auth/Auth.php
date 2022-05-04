<?php

namespace Teleclinic\ApiPatient\Auth;


use Company\Entity\Entity;
use Company\Exception\NotFoundException;
use Company\Exception\UnauthorizedException;
use Company\MVC\Bootstrap;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Respect\Validation\Exceptions\ValidationException;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAccountMapper;
use Teleclinic\ApiPatient\Model\ApiPatientPatientMapper;
use Teleclinic\ApiPatient\Model\PatientMapper;

class Auth extends \Slim\Middleware
{
    /**
     * @var Entity
     */
    private static $patient;
    private static $patientAccount;
    private static $instance;

    /**
     * @param $username
     * @param $password
     * @param $expireTime
     * @return array
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public static function login($username, $password, $expireTime): \Result
    {
        $rsLogin = ApiPatientPatientAccountMapper::makeInstance()->login($username, $password);
        if (!$rsLogin->getStatus())
            return $rsLogin;

        self::$patient = $rsLogin->getData()['patient'];
        self::$patientAccount = $rsLogin->getData()['patientAccount'];

        $acceptToken = self::makeInstance()->generateToken($expireTime);
        return (new \Result())->setSuccess([
            'acceptToken' => $acceptToken,
            'expireTime' => $expireTime,
            'acceptTokenType' => 'Bearer'
        ]);
    }

    /**
     * @param $exp
     * @param $patient
     * @return string
     * @throws NotFoundException
     */
    public function generateToken($exp, $patient = null, $patientAccount = null): string
    {
        if (is_null($patient)) {
            $patient = self::$patient;
            $patientAccount = self::$patientAccount;
        }

        if (!$patient->id || !$patientAccount->id)
            throw new NotFoundException('Patient is not found!', 404);

        $payload = array(
            "iss" => Bootstrap::getInstance()->config['jwtPatient']['iss'],
            "aud" => Bootstrap::getInstance()->config['jwtPatient']['aud'],
            "iat" => time(),
            "exp" => $exp,
            "nbf" => time(),
            "patient_id" => $patient->id,
            "patient_code" => $patient->code,
            "patient_account_id" => $patientAccount->id,
            "updatedAt" => $patientAccount->updatedAt, //Đánh dấu cập nhật lần cuối để logout token cũ
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
        self::$patient = null;
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

        self::$patient = $this->verifyToken($token);
        if (!self::$patient)
            throw new UnauthorizedException('Unauthorized', '401');
        return $this;
    }

    /**
     * @param string $token
     * @return Entity|null
     */
    public function verifyToken(string $token): ?Entity
    {
        try {
            if (trim($token) == '')
                return null;
            $decoded = JWT::decode($token, new Key(Bootstrap::getInstance()->config['jwtPatient']['secret'], 'HS256'));
            $account = ApiPatientPatientAccountMapper::makeInstance()
                ->filterStatus(ApiPatientPatientAccountMapper::STATUS_ACTIVE)
                ->filterID($decoded->patient_account_id)
                ->getEntity();

            if (!$account->id)
                return null;

            $patient = ApiPatientPatientMapper::makeInstance()
                ->filterCode($account->patientCode)
                ->filterID($decoded->patient_id)
                ->setAutoLoadAttrs()
                ->getEntity();
            return $patient->id ? $patient : null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function user()
    {
        return self::$patient;
    }

    /**
     * @param $patientCode
     * @param $password
     * @return int
     * @throws \Exception
     */
    public function changePassword($patientCode, $password)
    {
        return ApiPatientPatientAccountMapper::makeInstance()->filterPatientCode($patientCode)->update([
            'password' => md5($password),
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
    }

    public function call()
    {
        // TODO: Implement call() method.
    }
}