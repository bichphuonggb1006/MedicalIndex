<?php

namespace Teleclinic\Teleclinic\Model;

use Company\Entity\Entity;
use Company\Exception\BadRequestException;
use Company\Exception\NotFoundException;
use Company\File\Model\FileMapper;
use Company\Site\Model\SiteMapper;
use Company\SQL\Mapper;
use Company\User\Model\UserMapper;

class ScheduleMapper extends Mapper
{
    const STATUS_UNSCHEDULED = 'unscheduled';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FREE = 'free';

    protected $_autoloadFile = false;


    function tableName()
    {
        return 'teleclinic_schedule';
    }

    function tableAlias()
    {
        return 'schedule';
    }

    function autoloadFile()
    {
        $this->_autoloadFile = true;
        return $this;
    }

    /**
     * @param array $status
     * @return $this
     */
    function filterStatus($status)
    {
        if (count($status)) $this->filterIn('`status`', $status);
        return $this;
    }

    function filterPatientID($pid)
    {
        if ($pid) $this->where('patientID=?', __FUNCTION__)->setParamWhere($pid, __FUNCTION__);
        return $this;
    }

    function filterPatientName($name)
    {
        if ($name) $this->where('patientName LIKE ?', __FUNCTION__)->setParamWhere("%$name%", __FUNCTION__);
        return $this;
    }

    function filterClinic($clinicID)
    {
        if ($clinicID) $this->where('vclinicID=?', __FUNCTION__)->setParamWhere($clinicID, __FUNCTION__);
        return $this;
    }

    function filterSite($siteID)
    {
        if ($siteID) $this->where('siteID=?', __FUNCTION__)->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    public function filterServiceID($serviceID)
    {
        if ($serviceID) $this->where('reqServiceID=?', __FUNCTION__)->setParamWhere($serviceID, __FUNCTION__);
        return $this;
    }

    public function filterDoctorID($doctorID)
    {
        if ($doctorID) $this->where('doctorID=?', __FUNCTION__)->setParamWhere($doctorID, __FUNCTION__);
        return $this;
    }

    public function filterUID($uid)
    {
        if ($uid) $this->where('uid=?', __FUNCTION__)->setParamWhere($uid, __FUNCTION__);
        return $this;
    }

    function filterScheduledDate($date)
    {
        if ($date) {
            if (is_array($date)) {
                //from, to
                $from = \DateTimeEx::create($date[0])->setTime(0, 0, 0)->toIsoString();
                $to = \DateTimeEx::create($date[1])->setTime(23, 59, 59)->toIsoString();
                $this->where('scheduledDate >=? AND scheduledDate <=?', __FUNCTION__)
                    ->setParamWhere($from, __FUNCTION__ . 1)->setParamWhere($to, __FUNCTION__ . 2);
            } else {
                $date = \DateTimeEx::create($date);
                $this->where('scheduledDate >= ? AND scheduledDate <= ?', __FUNCTION__)
                    ->setParamWhere($date->toIsoString(false), __FUNCTION__ . 1)
                    ->setParamWhere($date->toIsoString(false) . ' 23:59:59', __FUNCTION__ . 2);
            }
        }
        return $this;
    }

    function filterScheduledDateIndex($date, $hour = 0)
    {
        if ($date && $hour == 0) {
            $date = \DateTimeEx::create($date);
            $this->where('scheduledDateIndex >= ? AND scheduledDateIndex <= ?', __FUNCTION__)
                ->setParamWhere($date->toIsoString(false), __FUNCTION__ . 1)
                ->setParamWhere($date->toIsoString(false) . ' 23:59:59', __FUNCTION__ . 2);
        } else if ($date && $hour > 0) {
            $date = \DateTimeEx::create($date)->setTime($hour, 0, 0);
            $this->where('scheduledDateIndex=?', __FUNCTION__)->setParamWhere($date->toIsoString(), __FUNCTION__);
        }
        return $this;
    }

    function filterReqDate($date)
    {
        if ($date) {
            $date = \DateTimeEx::create($date);
            $this->where('date(reqDate)=?', __FUNCTION__)->setParamWhere($date->toIsoString(false), __FUNCTION__);
        }
        return $this;
    }

    function filterPhone($phone)
    {
        if ($phone) $this->where('phone=?', __FUNCTION__)->setParamWhere($phone, __FUNCTION__);
        return $this;
    }

    function filterTreatmentType($treatmentType)
    {
        if ($treatmentType) $this->where('treatmentType=?', __FUNCTION__)->setParamWhere($treatmentType, __FUNCTION__);
        return $this;
    }

    function filterCreatedDate($date)
    {
        if ($date) {
            if (is_array($date)) {
                //from, to
                $from = \DateTimeEx::create($date[0])->setTime(0, 0, 0)->toIsoString();
                $to = \DateTimeEx::create($date[1])->setTime(23, 59, 59)->toIsoString();
                $this->where('createdTime >=? AND createdTime <=?', __FUNCTION__)
                    ->setParamWhere($from, __FUNCTION__ . 1)->setParamWhere($to, __FUNCTION__ . 2);
            } else {
                $date = \DateTimeEx::create($date);
                $this->where('createdTime >= ? AND createdTime <= ?', __FUNCTION__)
                    ->setParamWhere($date->toIsoString(false), __FUNCTION__ . 1)
                    ->setParamWhere($date->toIsoString(false) . ' 23:59:59', __FUNCTION__ . 2);
            }
        }
        return $this;
    }

    function filterPayStatus($status)
    {
        if (count($status) && is_array($status)) {
            $this->filterIn('`paymentStatus`', $status);
            return $this;
        }

        if ($status) $this->where('paymentStatus=?', __FUNCTION__)->setParamWhere($status, __FUNCTION__);
        return $this;
    }

    function makeEntity($rawData)
    {
        $row = parent::makeEntity($rawData);
        if ($row->patientAttr) {
            $row->patient = new Entity(json_decode($row->patientAttr, true));
            unset($row->patientAttr);
        }
        if ($row->reqServiceAttr) {
            $row->reqService = ServiceListMapper::makeInstance()->makeEntity(json_decode($row->reqServiceAttr, true));
            unset($row->reqServiceAttr);
        }
        if ($row->doctorAttr) {
            $row->doctor = UserMapper::makeInstance()->makeEntity((json_decode($row->doctorAttr, true)));
            unset($row->doctorAttr);
        }
        if ($row->vclinicAttr) {
            $row->vclinic = VclinicMapper::makeInstance()->makeEntity(json_decode($row->vclinicAttr, true));
            unset($row->vclinicAttr);
        }
        if ($row->id && $this->_autoloadFile) {
            $row->files = FileMapper::makeInstance()->selectForList()->filterContext("schedule:" . $row->id)
                ->getEntities()->toArray();
        }
        return $row;
    }

    function updateRequest($input)
    {
        $scheduleID = arrData($input, 'scheduleID');
        $paymentStatus = arrData($input, 'paymentStatus');
        $reqServiceID = arrData($input, 'reqServiceID');
        //cap nhat phuong thuc thanh toan
        $updateData = ['paymentStatus' => $paymentStatus];

        //check service exists
        $service = ServiceListMapper::makeInstance()->filterID($reqServiceID)->filterDeleted(0)
            ->getEntityOrFail(new NotFoundException("Service not found"));
        //cap nhat thong tin dich vu
        $updateData += ['reqServiceAttr' => json_encode($service),
            'reqServiceID' => $service->id];

        $this->startTrans();
        //cap nhat trang thai phi cua ca
        $this->filterID($scheduleID)->update($updateData);
        //insert files banking

        if (arrData($input, 'files')) {
            foreach ($input['files'] as $file) {
                $file += ['siteID' => $service->siteID,
                    'context' => 'schedule:' . $scheduleID];
                FileMapper::makeInstance()->updateFile(0, $file);
            }
        }

        $this->completeTransOrFail();


    }

    function newRequest($input)
    {

        //validate
        $updateData = ['reqDate' => arrData($input, 'reqDate'),
            'reqTimes' => arrData($input, 'reqTimes', []),
            'reqNote' => arrData($input, 'reqNote'),
            'reqServiceID' => arrData($input, 'reqServiceID'),
            'paymentStatus' => arrData($input, 'paymentStatus')];
        foreach ($updateData as $field => $val) {
            if (!$val) throw new BadRequestException("missing required fields: $field");
        }

        if (!is_array($updateData['reqTimes']) || !is_numeric($updateData['reqTimes'][0])) {
            throw new BadRequestException("reqTimes must be ['number', 'number']");
        }

        /* Kiem tra thoi gian yeu cau kham. TH qua khu thi bo qua, thong bao loi */
        $currDate = date("Y-m-d");
        if (strtotime($updateData['reqDate']) < strtotime($currDate)) {
            throw new BadRequestException("reqDate invalid");
        }

        $updateData['reqTimes'] = implode(',', $updateData['reqTimes']);

        if (!in_array($input['paymentStatus'], [static::PAYMENT_PAID,
            static::PAYMENT_UNPAID,
            static::PAYMENT_FREE])) throw new BadRequestException("invalid payment status");

        $patient = $this->validatePatientData($input['patient']);
        $updateData += ['patientID' => $patient['id'],
            'phone' => $patient['phone'],
            'patientName' => $patient['name'],
            'patientAttr' => json_encode($patient)];
        $updateData += ['paymentTransaction' => arrData($input, 'paymentTransaction')];
        $updateData += ['paymentFK' => arrData($input, 'paymentId')];
        //check service exists
        $service = ServiceListMapper::makeInstance()->filterID($updateData['reqServiceID'])->filterDeleted(0)
            ->getEntityOrFail(new NotFoundException("Service not found"));

        //update dieu tri covid Yersin
        if(arrData($input, 'isCovidRegis')){
            $updateData += ['startTreatmentDdata'=>json_encode(arrData($input, 'registrationCovidForm'))];
            $updateData +=['treatmentType'=>'covid'];
        }
        $service->reqHealthInsurance = arrData($input, 'reqHealthInsurance');
        $updateData += ['reqServiceAttr' => json_encode($service),
            'siteID' => $service->siteID,
            'status' => static::STATUS_UNSCHEDULED,
            'reqDate' => \DateTimeEx::create()->toIsoString(true)];

        $updateData["uid"] = uid();
//        $updateData["patientPassword"] = $this->generateRandomString(6);
        $updateData["patientPassword"] = "123456"; //fake password
        $updateData["createdTime"] = date("Y-m-d H:i:s");
        $this->startTrans();
        $this->insert($updateData);
        $id = $this->db->Insert_ID();
        //insert files
        if (arrData($input, 'files')) {
            foreach ($input['files'] as $file) {
                $file += ['siteID' => $service->siteID,
                    'context' => 'schedule:' . $id];
                FileMapper::makeInstance()->updateFile(0, $file);
            }
        }

        $this->completeTransOrFail();

        return result(true, ['id' => $id]);

    }

    function cancel($id, $comment)
    {
        if (!$comment) throw new BadRequestException("Comment cannot be null");

        $this->startTrans();
        static::makeInstance()->filterID($id)->update(['comment' => $comment,
            'status' => static::STATUS_CANCELLED]);
        $this->completeTransOrFail();

        return true;
    }

    function diagnosis($id, $input)
    {
        $requiredFields = ['doctorID'];
        foreach ($requiredFields as $field) {
            if (!arrData($input, $field)) throw new BadRequestException("missing required field: $field");
        }
        $allowedFields = array_merge($requiredFields, ['diagDesc',
            'diagConclusion',
            'diagRecommendation',
            'diagPrescription',
            'reExamDate']);
        $updateData = [];
        foreach ($allowedFields as $field) {
            $updateData[$field] = arrData($input, $field);
        }

        $doctor = UserMapper::makeInstance()->filterID($updateData['doctorID'])
            ->getEntityOrFail(new NotFoundException("User not found: " . $updateData['doctorID']));
        $updateData['diagDate'] = \DateTimeEx::create()->toIsoString();
        $updateData['doctorAttr'] = json_encode($doctor);
        $updateData += ['diagDate' => \DateTimeEx::create()->toIsoString(),
            'doctorAttr' => json_encode($doctor),
            'status' => static::STATUS_COMPLETED];

        $this->startTrans();
        $this->filterID($id)->update($updateData);
        if (arrData($input, 'createNextSchedule')) $this->createNextSchedule($id);
        $this->completeTransOrFail();
    }

    function createNextSchedule($currentID)
    {
        $currentSchedule = static::makeInstance()->filterID($currentID)->getRow();
        unset($currentSchedule['id']);
        $now = \DateTimeEx::create($currentSchedule['scheduledDate']);
        $reExamDate = $now->addDay($currentSchedule['reExamDate'])->setTime($now->format('H'), 0, 0);
        //tạo bản tái khám, một số thông tin lấy từ bản ghi cũ
        $newSchedule = ['status' => 'unscheduled',
                'reqDate' => $reExamDate->toIsoString(false),
                'reqNote' => '[Tái khám] ' . $currentSchedule['reqNote'],
                'paymentTransaction' => '',
                'paymentStatus' => 'unpaid',
                'scheduledDate' => $reExamDate->toIsoString(),
                'scheduledDateIndex' => $reExamDate->toIsoString(),
                'doctorID' => null,
                'doctorAttr' => null,
                'diagDate' => null,
                'diagDesc' => null,
                'diagConclusion' => null,
                'diagRecommendation' => null,
                'diagPrescription' => null,
                'reExamDate' => null,
                'uid' => uniqid(),
                'prevSchedule' => $currentID,
                'nextSchedule' => null] + $currentSchedule; //left override right array
        $this->insert($newSchedule);
        $newId = $this->db->Insert_ID();

        static::makeInstance()->filterID($currentID)->update(['nextSchedule' => $newId]);
    }

    protected function validatePatientData($data)
    {
        if (!is_array($data)) throw new BadRequestException("patient data must be array/object");
        $requiredFields = ['id',
            'name',
            'age',
            'phone'];
        foreach ($requiredFields as $field) {
            if (!strlen(arrData($data, $field))) throw new BadRequestException("missing required fields: $field");
        }
        $allowedFields = ['id',
            'name',
            'age',
            'phone',
            'addressText',
            'addressDetails',
            'email',
            'sex',
            'healthInsurance',
            'encounterID',
            'birthDate'];
        $update = [];
        foreach ($allowedFields as $field) {
            $update[$field] = arrData($data, $field);
        }
        return $update;
    }

    /**
     * Điều phối xác nhận lịch khám của bệnh nhân
     * @param $scheduledDate
     * @param $vclinicID
     */
    function confirmSchedule($id, $scheduledDate, $vclinicID)
    {
        //check
        if (!$scheduledDate || !\DateTimeEx::create($scheduledDate)) throw new BadRequestException("date not valid");
        $clinic = VclinicMapper::makeInstance()->filterID($vclinicID)
            ->getEntityOrFail(new NotFoundException("vclinic not found: $vclinicID"));

        $this->filterID($id)->update(['scheduledDate' => $scheduledDate,
            'scheduledDateIndex' => \DateTimeEx::create($scheduledDate)->format('Y-m-d H') . ':00:00',
            'vclinicID' => $vclinicID,
            'vclinicAttr' => json_encode($clinic),
            'status' => static::STATUS_SCHEDULED]);
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getMedicalRecord($siteID,$input){
//        $parentNode = $this->db->GetRow("SELECT * FROM $table WHERE " . $this->getPkField() . "=?", array(arrData($node, $parentCol)));
//        $childNodes = $this->db->GetAll("SELECT * FROM $table WHERE $parentCol=?", array($fromNode));
        $sql = "SELECT DISTINCT(patientID), patientName, patientAttr FROM teleclinic_schedule WHERE siteID = ?";

        $arrParams[] = $siteID;

        if(arrData($input,'name')){
            $sql .= " AND `patientName` LIKE '%".$input['name']."%' ";
           // $arrParams[] = $input['name'];
        }

        if(arrData($input,'phone')){
            $sql .= ' AND patientID = ?';
            $arrParams[] = $input['phone'];
        }
        $sql .= ' ;';
        return $this->db->getAll($sql, $arrParams);

    }

    function newScheduleDetails($inputs){
        //processData
        $this->startTrans();

        $this->db->insert("teleclinic_schedule_detail", $inputs);
        $id = $this->db->Insert_ID();
        $this->completeTransOrFail();

        return result(true, ['id' => $id]);

    }


    function updateStopProcess($input)
    {
        $scheduleID = arrData($input, 'scheduleID');
        if (!$scheduleID) throw new BadRequestException("scheduleID is not valid");

        $updateData = [
            'status' => 'completed',
            'endTreatmentDdata' => json_encode($input)
        ];

        $this->startTrans();
        //cap nhat trang thai phi cua ca
        $this->filterID($scheduleID)->update($updateData);

        $this->completeTransOrFail();
        return result(true, []);
    }

    //get Ho so kham
    function getPatientData($phoneNumber){
        $schedules = $this->filterPhone($phoneNumber)->orderBy("diagDate desc")->getAll()
            ->toArray();

        $res = [    "schedules" => [],
                    "patientInformation" => []
                ];
        $res["patientInformation"] = json_decode($schedules[0]["patientAttr"], true);
        $sites = SiteMapper::makeInstance()->getAll()->toArray();
        $sites = array_combine(array_column($sites, "id"), $sites);


        foreach ($schedules as $schedule) {
            if ($schedule["status"] == ScheduleMapper::STATUS_COMPLETED)
                $res["schedules"][] = ["diagnosis" => ["diagDesc" => $schedule["diagDesc"],
                    "diagConclusion" => $schedule["diagConclusion"],
                    "diagRecommendation" => $schedule["diagRecommendation"],
                    "diagPrescription" => $schedule["diagPrescription"],
                    "reExamDate" => $schedule["reExamDate"],],
                    "siteName" => $sites[$schedule["siteID"]]["name"],
                    "serviceName" => arrData(json_decode($schedule["reqServiceAttr"], true), "name"),
                    "scheduledDate" => date("Y-m-d", strtotime($schedule["scheduledDate"])),
                    "scheduledTime" => date("H:i:s", strtotime($schedule["scheduledDate"])),
                    "doctorName" => arrData(json_decode($schedule["doctorAttr"], true), "fullname")

                ];
        }

        return $res;
    }

}