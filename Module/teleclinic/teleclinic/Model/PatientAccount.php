<?php

namespace Teleclinic\Teleclinic\Model;

/**
 * @property string $id
 * @property string $patientCode
 * @property string $username
 * @property string $password
 * @property int $status
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $deletedAt
 * @property string $verifyAt
 */
class PatientAccount extends AbstractMapper
{
    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_BLOCKED = 3;

    function tableName()
    {
        return 'teleclinic_patient_account';
    }

    function tableAlias()
    {
        return 't_p_a';
    }

    function __construct()
    {
        parent::__construct();
    }

    public function updatePassword($patientCode, $password): bool
    {
        return self::makeInstance()->filterPatientCode($patientCode)->update([
            'password' => md5($password)
        ]);
    }

    public function filterPassword($password)
    {
        $this->where('`password`=?', __FUNCTION__)->setParamWhere(md5($password), __FUNCTION__);
        return $this;
    }

    public function filterPatientCode($patientCode)
    {
        $this->where('`patientCode`=?', __FUNCTION__)->setParamWhere($patientCode, __FUNCTION__);
        return $this;
    }

    public function filterUsername($username)
    {
        $this->where('username=?', __FUNCTION__)->setParamWhere($username, __FUNCTION__);
        return $this;
    }

    public function filterStatus($status)
    {
        $this->where('`status`=?', __FUNCTION__)->setParamWhere(self::STATUS_ACTIVE, __FUNCTION__);
        return $this;
    }

    public function filterTokenResetPassword($token)
    {
        $this->where('`token_reset_password`=?', __FUNCTION__)->setParamWhere($token, __FUNCTION__);
        return $this;
    }

}