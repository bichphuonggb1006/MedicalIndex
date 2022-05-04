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
use function GuzzleHttp\Psr7\str;

/**
 * @property  string $fullName
 * @property  string $code
 * @property string $id
 * @property int $status
 * @property string $statusText
 * @property string $birthDate
 * @property array $files
 */
class PatientMapper extends AbstractMapper
{
    const STATUS_NEW = 1;
    const STATUS_ACTIVATED = 2;
    const STATUS_BAN = 3;
    const SEX_FEMALE = 'F';
    const SEX_MALE = 'M';
    const TYPE_COVID = 'COVID';
    const DISABLED = 1;
    const ENABLED = 0;
    protected $_autoloadAttrs = false;

    public static function allSex()
    {
        return [
            self::SEX_FEMALE => 'Nữ',
            self::SEX_MALE => 'Nam',
        ];
    }

    public static function allStatus()
    {
        return [
            self::STATUS_NEW => 'Chưa kích hoạt',
            self::STATUS_ACTIVATED => 'Đã kích hoạt',
            self::STATUS_BAN => 'Cấm truy cập',
        ];
    }

    function tableName()
    {
        return 'teleclinic_patient';
    }

    function tableAlias()
    {
        return 'patient';
    }

    public function setAutoLoadAttrs()
    {
        $this->_autoloadAttrs = true;
        return $this;
    }

    function autoloadFile()
    {
        $this->_autoloadFile = true;
        return $this;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function makeEntity($rawData)
    {

        $rawData = parent::makeEntity($rawData); // TODO: Change the autogenerated stub
        if ($this->_autoloadAttrs) {
            $patientAttrs = PatientAttrMapper::makeInstance()->filterPatientCode($rawData->code)->getEntities();
            foreach (PatientAttrMapper::$attrsKey as $attrKey => $attrName) {
                $rawData->{$attrKey} = null;
                foreach ($patientAttrs as $patientAttr) {
                    if ((string)$patientAttr->attrCode === (string)$attrKey) {
                        $rawData->{$attrKey} = $patientAttr->attrValue;
                    }
                }
            }
        }
        return $rawData;
    }


    /**
     * @param array $status
     * @return $this
     */
    function filterStatus($status)
    {
        if (count($status)) $this->filterIn('status', $status);
        return $this;
    }

    function filterFullNameIsEmpty()
    {
        $this->where('(fullName= "" or fullName is null)', __FUNCTION__);
        return $this;
    }

    function filterIsCovid()
    {
        $this->where('(type= ?)', __FUNCTION__)->setParamWhere(self::TYPE_COVID, __FUNCTION__);
        return $this;
    }

    public function filterEnabled()
    {
        $this->where('(disabled != 1)', __FUNCTION__);
        return $this;
    }

    public function filterPhone($phone)
    {
        $this->where('phone=?', __FUNCTION__)->setParamWhere($phone, __FUNCTION__);
        return $this;
    }

    public function filterCode($code)
    {
        $this->where('code=?', __FUNCTION__)->setParamWhere($code, __FUNCTION__);
        return $this;
    }

    /**
     * @param $cardID
     * @return $this
     */
    public function filterCardID($cardID)
    {
        $this->where('cardID=?', __FUNCTION__)->setParamWhere($cardID, __FUNCTION__);
        return $this;
    }

    public function filterEmail($Email)
    {
        $this->where('email=?', __FUNCTION__)->setParamWhere($Email, __FUNCTION__);
        return $this;
    }

    public function filterPassword($password)
    {
        $this->where('password=?', __FUNCTION__)->setParamWhere($password, __FUNCTION__);
        return $this;
    }


    /**
     * @param array $patientData
     * @param string|null $patientID
     * @return array
     * @throws \Exception
     */
    public function storeOrUpdate(array $patientData, string $patientID = null)
    {
        //Update vào trường tường minh của table
        $updateFieldData = [];
        $columnOfTable = DB::getInstance()->Execute("SHOW COLUMNS FROM " . self::makeInstance()->tableName())->GetArray();
        foreach ($columnOfTable as $row) {
            $colName = $row['Field'];
            if (isset($patientData[$colName])) {
                $updateFieldData[$colName] = $patientData[$colName];
            }
        }

        //Chỉ định update dữ liệu cho trường động trong attrs
        $attrs = [];
        $attrCols = ["medicalHistory", 'temporaryResidence', 'permanentResidence', 'organization'];
        foreach ($attrCols as $colName) {
            if (in_array($colName, $attrCols)) {
                $attrs[$colName] = arrData($patientData, $colName);
            }
        }
        $validate = $this->checkValidate($updateFieldData, $attrs, $patientID);
        if ($validate['status'] === false) {
            return $validate;
        }

        $updateFieldData = $validate['data'];
        $this->startTrans();
        if (!$patientID) {
            $patientID = uid();
            $code = uid();
            $updateFieldData['id'] = $patientID;
            $updateFieldData['code'] = $code;
            $updateFieldData['organizationID'] = isset($patientData['organization']['organizationID']) ? $patientData['organization']['organizationID'] : null;
            $updateFieldData['attrs'] = json_encode($updateFieldData['attrs']);
            if (PatientMapper::makeInstance()->insert($updateFieldData) === false)
                throw new \Exception('Save patient failed');
            $password = trim($patientData['password']) == '' ? $updateFieldData['phone'] : $patientData['password'];
            $this->updatePassword($patientID, $password);
        } else {
            if ($updateFieldData['attrs']) {
                $updateFieldData['attrs'] = json_encode($updateFieldData['attrs']);
                $updateFieldData['organizationID'] = isset($patientData['organization']['organizationID']) ? $patientData['organization']['organizationID'] : null;
            }
            PatientMapper::makeInstance()->filterID($patientID)->update($updateFieldData);
        }
        $this->completeTransOrFail();
        $patientEntity = PatientMapper::makeInstance()->filterID($patientID)->getEntity();
        return result(true, $patientEntity, 200);
    }

    //Lấy cả xóa mềm
    public function withTrashed()
    {
        $this->where('(deletedAt is null or deletedAt is not null )', __FUNCTION__);
        return $this;
    }

    //Chỉ lấy xóa mềm
    public function onlyTrashed()
    {
        $this->where('deletedAt is not null', __FUNCTION__);
        return $this;
    }

    //Chỉ lấy bản ghi chưa xóa
    public function withOutTrashed()
    {
        $this->where('deletedAt is null', __FUNCTION__);
        return $this;
    }

    public function trashed($id)
    {
        $patientEntity = self::makeInstance()
            ->withOutTrashed()
            ->filterID($id)
            ->getEntity();
        if (!$patientEntity->id)
            return result(false, 'Patient not found', 400);

        $delete = self::makeInstance()->filterID($id)->update([
            'deletedAt' => date('Y-m-d H:i:s'),
            'cardID' => $patientEntity->cardID . '$$' . date('Ymdhis'),
            'email' => $patientEntity->email . '$$' . date('Ymdhis'),
            'phone' => $patientEntity->phone . '$$' . date('Ymdhis'),
        ]);
        if ($delete == false)
            throw new Exception('Delete patient failed');
        return result(true, true, 200);
    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function ban($id)
    {
        $rs = self::makeInstance()
            ->withOutTrashed()
            ->filterID($id)->update([
                'status' => self::STATUS_BAN,
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
        return result(true, true, 200);
    }

    /**
     * @param $siteID
     * @return $this
     */
    public function filterSiteID($siteID)
    {
        $this->where('siteID=?', __FUNCTION__)
            ->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }




    public function filterLikeFullName($fullName = '')
    {
        if (!empty($fullName))
            $this->where('fullName like ?', __FUNCTION__)
                ->setParamWhere("%$fullName%", __FUNCTION__);
        return $this;
    }

    public function filterLikePhone($phone = '')
    {
        if (!empty($phone))
            $this->where('phone like ?', __FUNCTION__)
                ->setParamWhere("%$phone%", __FUNCTION__);
        return $this;
    }


    public function filterProvinceOfTemporaryResidence($province)
    {
        if (!empty($province))
            $this->where("JSON_EXTRACT(attrs,'$.temporaryResidence.province') = ?", __FUNCTION__)
                ->setParamWhere("$province", __FUNCTION__);
        return $this;
    }

    public function filterpermanentResidence($permanentResidenceAddress)
    {
        if (!empty($permanentResidenceAddress))
            $this->where("JSON_EXTRACT(attrs,'$.permanentResidence.address') like ?", __FUNCTION__)
                ->setParamWhere("%$permanentResidenceAddress%", __FUNCTION__);
        return $this;
    }

    public function filterOrganizationID($term)
    {
        if ((string)$term === '-1') {
            $this->where("organizationID is null", __FUNCTION__);
        } else {
            $this->where("organizationID=?", __FUNCTION__)
                ->setParamWhere("$term", __FUNCTION__);
        }

        return $this;
    }

    public function filterSex($term)
    {
        $this->where("sex=?", __FUNCTION__)
            ->setParamWhere($term, __FUNCTION__);
        return $this;
    }


    public function filterHardLabor($term)
    {
        $this->where("JSON_EXTRACT(attrs,'$.organization.hardLabor') = ?", __FUNCTION__)
            ->setParamWhere($term ? "1" : "0", __FUNCTION__);
        return $this;
    }

    public function filteLikerHardLabor($term)
    {
        $this->where("JSON_EXTRACT(attrs,'$.organization.hardLabor') = ?", __FUNCTION__)
            ->setParamWhere($term, __FUNCTION__);
        return $this;
    }

    public function filterBirthDate($term)
    {
        $this->where("birthDate = ?", __FUNCTION__)
            ->setParamWhere($term, __FUNCTION__);
        return $this;
    }

    public function filterLikeJoinDate($term)
    {
        $this->where("JSON_EXTRACT(attrs,'$.organization.joinDate') like ?", __FUNCTION__)
            ->setParamWhere("%$term%", __FUNCTION__);
        return $this;

    }

    public function filterLikeBirthDate($term)
    {
        $this->where("birthDate like ?", __FUNCTION__)
            ->setParamWhere("%$term%", __FUNCTION__);
        return $this;
    }

    public function filterLikeJob($term)
    {
        $this->where("JSON_EXTRACT(attrs,'$.organization.jobTitle') like ?", __FUNCTION__)
            ->setParamWhere("%$term%", __FUNCTION__);
        return $this;
    }

    public function filterLikePatientCode($term)
    {
        if (!empty($term))
            $this->where("JSON_EXTRACT(attrs,'$.organization.patientCode') like ?", __FUNCTION__)
                ->setParamWhere("%$term%", __FUNCTION__);
        return $this;
    }

    public function filterLikeCardID($cardID)
    {
        $this->where('cardID like ?', __FUNCTION__)->setParamWhere("%$cardID%", __FUNCTION__);
        return $this;
    }

    public function filterLikeEmail($Email)
    {
        $this->where('email like ?', __FUNCTION__)->setParamWhere("%$Email%", __FUNCTION__);
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function filterNotType($type)
    {
        $this->where("(type !=? or type is null)", __FUNCTION__)
            ->setParamWhere($type, __FUNCTION__);
        return $this;
    }
}