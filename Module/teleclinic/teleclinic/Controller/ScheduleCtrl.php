<?php

namespace Teleclinic\Teleclinic\Controller;

use Company\Exception\BadRequestException;
use Company\Exception\NotFoundException;
use Company\File\Model\FileMapper;
use Company\MVC\Controller;
use Company\MVC\Json;
use Company\MVC\MvcContext;
use Company\Setting\Model\FieldMapper;
use Company\Setting\Model\SettingDataMapper;
use Company\Site\Model\SiteMapper;
use Teleclinic\Teleclinic\Model\ScheduleMapper;
use Teleclinic\Teleclinic\Model\VclinicMapper;
use Telerivet_API;
use VrZip\Exception;
use Payment\BASE\Model\PaymentMapper;

class ScheduleCtrl extends Controller
{
    const SMS_SCHEDULE = 1;
    const SMS_NOTICE_RESULT = 2;
    const SMS_NOTICE_PAYMENT = 3;

    function getSchedules()
    {
        Auth::getInstance()->requireLogin();
        $siteID = $this->req->get('siteID');

        if (!$siteID) throw new BadRequestException("siteID must not null");

        $rs = ScheduleMapper::makeInstance()->filterStatus($this->req->get('status'))->filterSite($siteID)
            ->filterClinic($this->req->get('clinicID'))->filterPatientID($this->req->get('patientID'))
            ->filterScheduledDate($this->req->get('scheduledDate'))->filterReqDate($this->req->get('reqDate'))
            ->filterPatientName($this->req->get('patientName'))->filterServiceID($this->req->get('reqServiceID'))
            ->filterUID($this->req->get('uid'))->orderBy($this->req->get('order'))->getEntities();

        $this->outputJSON($rs);
    }

    function getSchedule($id)
    {
        Auth::getInstance()->requireLogin();

        $row = ScheduleMapper::makeInstance()->autoloadFile()->filterID($id)->getEntity();

        $this->outputJSON($row);
    }

    function getFile($id)
    {
        Auth::getInstance()->requireLogin();

        $file = FileMapper::makeInstance()->filterID($id)->getEntityOrFail();
        $file->outputFile();
    }

    function updatePaymentStatus($scheduleID)
    {
        Auth::getInstance()->requireLogin();

        $paymentStatus = $this->input('paymentStatus');
        if ($paymentStatus != 'paid') $paymentStatus = 'unpaid';

        ScheduleMapper::makeInstance()->filterID($scheduleID)->update(['paymentStatus' => $paymentStatus]);

        $this->outputJSON(result(true));
    }

    function newRequest()
    {
        $paymentInfo = arrData($this->input(), 'paymentInfo');
        $patient = arrData($this->input(), 'patient', []);
        $siteFK = arrData($this->input(), 'siteFK');
        $amount = arrData($this->input(), 'amount');
        $paymentId = 0;
        $paymentData = [];


        if (strlen($paymentInfo)) {
            $paymentData = ['orderType' => 'PATIENT_PAYMENT',
                'orderID' => PaymentMapper::makeInstance()->generateUniqID($paymentInfo),
                'status' => 'unpaid',
                'userID' => arrData($patient, 'id'),
                'userName' => arrData($patient, 'name'),
                'userPhone' => arrData($patient, 'phone'),
                'userAddress' => arrData($patient, 'addressText'),
                'userEmail' => '',
                'amount' => $amount,
                'payment' => $paymentInfo,
                'paymentInfo' => '',
                'paymentContent' => arrData($patient, 'name') . "- Khám online",
                'security' => '',
                'siteID' => $siteFK,
                'createdTime' => \DateTimeEx::create()->toIsoString(true)];

            $paymentRs = PaymentMapper::makeInstance()->newPayment($paymentData);
            $paymentRsData = arrData($paymentRs, 'data', []);
            $paymentId = arrData($paymentRsData, 'id');
            if (strlen($paymentId) && intval($paymentId)) {
                $paymentData['id'] = $paymentId;
            }
        }

        $reqServiceIDs = arrData($this->input(), 'reqServiceIDs', []);
        // TH cũ, ko phải TH nhiều DV
        if (!count($reqServiceIDs)) {
            $inputs = $this->input();
            $inputs['paymentId'] = $paymentId;
            $result = ScheduleMapper::makeInstance()->newRequest($inputs);
            $result['data']['payment'] = $paymentData;

            return $this->resp->setBody($result);
        }

        /* Tách yêu cầu đăng ký khám thành các y.c nhỏ */
        $requestTimes = arrData($this->input(), 'reqTimes', []);
        if (!count($requestTimes)) {
            throw new \Company\Exception\BadRequestException("request times empty");
        }

        $resp = result(false, ['schedules' => []]);
        foreach ($requestTimes as $srvID => $req) {
            $inputs = ['patient' => arrData($this->input(), 'patient', []),
                'reqDate' => arrData($req, 'reqDate'),
                'reqTimes' => arrData($req, 'reqTimes', []),
                'reqNote' => arrData($this->input(), 'reqNote'),
                'reqServiceID' => $srvID,
                'paymentStatus' => arrData($this->input(), 'paymentStatus'),
                'reqHealthInsurance' => arrData($this->input(), 'reqHealthInsurance', []),
                'paymentId' => $paymentId,
                'files' => arrData($this->input(), 'files', []),
                'registrationCovidForm' => arrData($this->input(), 'registrationCovidForm',[]),
                'isCovidRegis' => arrData($this->input(), 'isCovidRegis',0)

            ];
            //them moi benh nhan vao bang teleclinic_patient , lam sau
            if(arrData($this->input(), 'isCovidRegis',0)){
//                insert table
            }
            $result = ScheduleMapper::makeInstance()->newRequest($inputs);

            $resp['status'] = $result['status'];
            $resp['data']['schedules'][] = ['scheduleID' => $result['data']['id'],
                'reqServiceID' => $srvID,
                'paymentStatus' => arrData($this->input(), 'paymentStatus')];
        }

        $resp['data']['patient'] = arrData($this->input(), 'patient', []);
        $resp['data']['payment'] = $paymentData;

        $this->resp->setBody($resp);
    }

    function updateRequest()
    {
        $reqServiceIDs = arrData($this->input(), 'reqServiceIDs', []);
        // TH cũ, ko phải TH nhiều DV
        if (!count($reqServiceIDs)) {
            ScheduleMapper::makeInstance()->updateRequest($this->input());
            return $this->resp->setBody(result());
        }

        // TH chuyển nhiều dv
        $schedules = arrData($this->input(), 'schedules', []);
        // lấy thông tin file nếu có (TH đính kèm thanh toán)
        $files = arrData($this->input(), 'files', []);

        if (!count($schedules)) {
            throw new \Company\Exception\BadRequestException("schedule empty");
        }

        foreach ($schedules as $input) {
            $input['files'] = $files;
            ScheduleMapper::makeInstance()->updateRequest($input);
        }

        $this->resp->setBody(result(true));
    }


    function confirmSchedule($id)
    {
        Auth::getInstance()->requireLogin();

        /* valid du lieu */
        /* Kiểm tra phòng khám ảo có lịch trực hôm đó không? */
        if (!\DateTimeEx::create($this->input('scheduledDate'))) throw \Exception("Schedule date invalid");

        $vcClinic = VclinicMapper::makeInstance()->filterID($this->input('vclinicID'))->getEntity();

        $weekDay = \DateTimeEx::create($this->input('scheduledDate'))->format('w');
        $hour = \DateTimeEx::create($this->input('scheduledDate'))->format('H');

        /* PK chưa có lịch trục hoặc ko có lịch trực trong ngày đã chọn */
        if (!count($vcClinic->schedule)) {
            throw new \Company\Exception\BadRequestException("schedule  empty");
        }

        if (!isset($vcClinic->schedule[$weekDay])) {
            throw new \Company\Exception\BadRequestException("schedule time invalid");
        }

        /* Kiểm tra thời gian chọn */
        /* check có được đăng ký khoảng giờ này ko */
        $beginHour = $vcClinic->schedule[$weekDay][0];
        $endHour = $vcClinic->schedule[$weekDay][1];
        if ($hour < $beginHour || $hour > $endHour) {
            throw new \Company\Exception\BadRequestException("schedule time invalid");
        }

        ScheduleMapper::makeInstance()->confirmSchedule($id, $this->input('scheduledDate'), $this->input('vclinicID'));
        $this->resp->setBody(result());
    }

    function diagnosis($id)
    {
        Auth::getInstance()->requireLogin();

        $input = $this->input();
        $input['doctorID'] = Auth::getInstance()->getUser()->id;
        ScheduleMapper::makeInstance()->diagnosis($id, $input);
        $this->resp->setBody(result());
    }

    function cancel($id)
    {
        Auth::getInstance()->requireLogin();

        ScheduleMapper::makeInstance()->cancel($id, $this->input('comment'));
        $this->resp->setBody(result());
    }

    /**
     * API cho màn hình tổng hợp theo phòng khám
     * @throws \Company\Exception\NotFoundException
     * @throws \Company\Exception\UnauthorizedException
     */
    function getClinicScheduleSummaries()
    {
        Auth::getInstance()->requireLogin();

        $clinicID = $this->req->get('clinicID');
        $date = $this->req->get('scheduledDate');

        $clinic = VclinicMapper::makeInstance()->filterID($clinicID)->getEntityOrFail();
        $rows = ScheduleMapper::makeInstance()->select('count(*) AS num, vclinicID,`scheduledDateIndex`, `status`')
            ->filterClinic($clinicID)->filterScheduledDateIndex($date)->groupBy('scheduledDateIndex, `status`')
            ->orderBy('scheduledDateIndex')->getAll();
        $ret = [];
        foreach ($rows as $row) {
            $hour = (int)date("H", strtotime($row["scheduledDateIndex"]));
            if (!isset($ret[$hour])) $ret[$hour] = [];
            $ret[$hour][$row['status']] = $row['num'];
        }
        $this->outputJSON($ret);
    }

    function sendNotification($siteID)
    {
        Auth::getInstance()->requireLogin();
        Auth::getInstance()->checkSiteID($siteID);

        $type = $this->input("type");

        $phoneNumber = $this->input("phoneNumber");

        if (!$phoneNumber) {
            $this->resp->setBody(json_encode(result(false, "Phone Number invalid")));
            return;
        }

        $site = SiteMapper::makeInstance()->filterID($siteID)->getRow();
        $siteName = $site["name"];

        $scheduleDatetime = $this->input("scheduleDatetime");

        if (!$scheduleDatetime) {
            $this->resp->setBody(json_encode(result(false, "Schedule Datetime invalid")));
            return;
        }

        $uid = $this->input("uid");
        if (!$uid) {
            $this->resp->setBody(json_encode(result(false, "uid invalid")));
            return;
        }

        $domainName = SettingDataMapper::makeInstance()->getSetting($siteID, "Domain");
        if ($type == self::SMS_SCHEDULE) {
            $content = "$siteName, lịch khám lúc $scheduleDatetime, đến giờ khám vui lòng truy cập địa chỉ $domainName/kham/$uid";
            file_put_contents("sms.txt", $content . "\n", FILE_APPEND);
        } elseif ($type == self::SMS_NOTICE_RESULT) {
            $password = $this->input("password");
            if (!$password) {
                $this->resp->setBody(json_encode(result(false, "password invalid")));
                return;
            }

            $content = "$siteName, Lịch khám lúc $scheduleDatetime đã có kết quả, vui lòng xem kết quả tại địa chỉ $domainName/benhnhan, mật khẩu: $password";
            file_put_contents("sms.txt", $content . "\n", FILE_APPEND);
        } else if ($type == self::SMS_NOTICE_PAYMENT) {
            $siteSetting = json_decode(SettingDataMapper::makeInstance()->getSetting($siteID, "SiteConfig"), true);
            $charge = arrData($siteSetting, "charge", []);
            if (!count($charge)) {
                $this->resp->setBody(json_encode(result(false, "sms not send")));
                return;
            }

            $createdTime = \DateTimeEx::create(arrData($this->input(), 'createdTime'))->format("Y-m-d");

            $scheduleUnpaid = ScheduleMapper::makeInstance()->filterPatientID($phoneNumber)
                ->filterStatus(['unscheduled',
                    'scheduled',
                    'completed'])->filterPayStatus("unpaid")->filterCreatedDate($createdTime)->getAll();

            if (!count($scheduleUnpaid)) {
                $this->resp->setBody(json_encode(result(false, "schedule unpaid empty")));
                return;
            }

            // tinh toan tong so tien
            $prices = 0;
            foreach ($scheduleUnpaid as $schedule) {
                $reqService = Json::decode(arrData($schedule, 'reqServiceAttr'));
                $price = intval(arrData($reqService, 'price'));
                if (!$price) continue;

                $prices += $price;
            }

            if (!$prices) {
                $this->resp->setBody(json_encode(result(false, "schedule unpaid empty")));
                return;
            }

            $sms = arrData($charge[0], 'sms');
            $unit = arrData($sms, "unit", $siteName);
            $date = \DateTimeEx::create($createdTime)->format("d/m/Y");

            if (count($charge) == 1) {
                $owner = arrData($sms, "owner", arrData($charge[0], 'owner'));
                $bank = arrData($sms, "bank", arrData($charge[0], 'bank'));
                $stk = arrData($charge[0], 'stk');
                $content = "{$unit}: TB thanh toan {$prices}d DV phi kham dang ky ngay {$date}. STK {$stk}, {$owner}, {$bank}";
            } else if (count($charge) > 1) {
                $content = "{$unit}: TB thanh toan {$prices}d DV phi kham dang ky ngay {$date}.";
                $idx = 1;
                foreach ($charge as $chr) {
                    $sms = arrData($chr, 'sms');
                    $owner = arrData($sms, "owner", arrData($chr, 'owner'));
                    $bank = arrData($sms, "bank", arrData($chr, 'bank'));
                    $stk = arrData($chr, 'stk');
                    $content .= " STK {$idx}: {$stk}";
                    if (strlen($owner)) {
                        $content .= ", {$owner}";
                    }

                    $content .= ", {$bank}.";
                    $idx++;
                }
            }

            $content = tiengVietKhongDau($content);
            file_put_contents("sms.txt", $content . "\n", FILE_APPEND);
        } else {
            $this->resp->setBody(json_encode(result(false, "sms type invalid")));
            return;
        }

        $API_KEY = 'jO2nM_L0K7D8fZEI857UmOBfdt3ebbng1EHd';           // from https://telerivet.com/api/keys
        $PROJECT_ID = 'PJ0b74464d0b394299';

        require_once dirname(__DIR__) . "/vendor/telerivet/telerivet-php-client/telerivet.php";

        $telerivet = new Telerivet_API($API_KEY);

        $project = $telerivet->initProjectById($PROJECT_ID);

        // Send a SMS message
        $project->sendMessage(array('to_number' => $phoneNumber,
            'content' => $content));

        $this->resp->setBody(result(true));
    }

    function startVideoCall($uid)
    {
        $schedule = ScheduleMapper::makeInstance()->filterUID($uid)->getRow();
        if (!$schedule) throw new \Company\Exception\NotFoundException();

//        if ($schedule["paymentStatus"] == ScheduleMapper::PAYMENT_UNPAID)
//            throw new \Exception("Chưa thanh toán");

        if ($schedule["status"] != ScheduleMapper::STATUS_SCHEDULED) throw new \Exception("Chưa xếp lịch");

        $scheduledDate = $schedule["scheduledDate"];
        $scheduledDate = date("Y-m-d", strtotime($scheduledDate));
        $now = date("Y-m-d");

        if ($scheduledDate != $now) throw new \Company\Exception\NotFoundException("Không phải thời gian khám");

        $room = arrData(json_decode($schedule["vclinicAttr"], true), ["videoCall",
            "room"]);

        if (!$room) throw new \Company\Exception\NotFoundException("Không tìm thấy phòng họp");

        header("Location: https://zoom.us/j/$room");
        exit;
    }

    function getScheduleHistory()
    {
        $phoneNumber = $this->input("phone");

        if (!$phoneNumber) {
            $this->resp->setBody(json_encode(result(false, "phone number not found")));
            return;
        }

        $password = $this->input("password");

        $schedules = ScheduleMapper::makeInstance()->filterPhone($phoneNumber)->orderBy("diagDate desc")->getAll()
            ->toArray();

        if (empty($schedules)) {
            $this->resp->setBody(json_encode(result(false, "schedule not found")));
            return;
        }

        $passwordList = array_column($schedules, "patientPassword");

        $res = ["schedules" => []];
        if (in_array($password, $passwordList)) {
            $sites = SiteMapper::makeInstance()->getAll()->toArray();
            $sites = array_combine(array_column($sites, "id"), $sites);

            $res["patientInformation"] = json_decode($schedules[0]["patientAttr"], true);
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
        } else {
            $this->resp->setBody(json_encode(result(false, "password doesn't match")));
            return;
        }

        $this->resp->setBody(json_encode(result(true, $res)));
    }

    function getTreatmentProcess()
    {
        $phoneNumber = $this->input("phone");

        if (!$phoneNumber) {
            $this->resp->setBody(json_encode(result(false, "phone number not found")));
            return;
        }

        $password = $this->input("password");

        $schedules = ScheduleMapper::makeInstance()->filterPhone($phoneNumber)->filterTreatmentType('covid')->filterStatus(['unscheduled'])->orderBy("id desc")->getAll()
            ->toArray();

        if (empty($schedules)) {
            $this->resp->setBody(json_encode(result(false, "Chưa đăng ký điều trị Covid")));
            return;
        }


        $passwordList = array_column($schedules, "patientPassword");

        $res = [];
        if (in_array($password, $passwordList)) {
            $sites = SiteMapper::makeInstance()->getAll()->toArray();
            $sites = array_combine(array_column($sites, "id"), $sites);

            $res["patientInformation"] = json_decode($schedules[0]["patientAttr"], true);
            $res["schedules"] = $schedules[0];
        } else {
            $this->resp->setBody(json_encode(result(false, "Mật khẩu không trùng khớp")));
            return;
        }

        $this->resp->setBody(json_encode(result(true, $res)));

    }

    function updateTreatmentProcess(){
        $patientInfo = arrData($this->input(), 'patient', []);
        $scheduleID  = arrData($this->input(), 'scheduleID','');
        $treatmentData = arrData($this->input(), 'process',[]);

         $inputs = [
             'patientID' =>$patientInfo['id'],
             'scheduleID' => $scheduleID,
             'treatmentData' => json_encode($treatmentData),
             'createdTime' =>\DateTimeEx::create()->toIsoString(true)
         ];

        $res = ScheduleMapper::makeInstance()->newScheduleDetails($inputs);
        $this->resp->setBody(json_encode($res));

    }

    function updateStopProcess(){
        $res = ScheduleMapper::makeInstance()->updateStopProcess($this->input());
        $this->resp->setBody(json_encode($res));
    }
    function baoCaoTongHopDichVu()
    {
        Auth::getInstance()->requireAdmin();

    }
}