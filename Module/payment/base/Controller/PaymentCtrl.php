<?php

namespace Payment\BASE\Controller;

use Company\Exception\BadRequestException;
use Company\MVC\Controller;
use Company\MVC\Json;
use Company\Site\Model\SiteMapper;
use Payment\BASE\Model\PaymentMapper;
use Company\Setting\Model\SettingDataMapper;

class PaymentCtrl extends Controller
{
    protected function init()
    {
        parent::init();
    }

    function generateUniqID($provider)
    {
        $orderID = PaymentMapper::makeInstance()->generateUniqID($provider);
        $this->resp->setBody(json_encode(result(true, ['orderID' => $orderID])));
    }

    function getPayments()
    {
        $inputs = $_GET;

        /* Kiểm tra xác thực chữ ký */
        $phone = arrData($inputs, 'phone');
        $signature = arrData($inputs, 'signature');
        $query = ['phone' => $phone,
            'salt' => PaymentMapper::PAYMENT_SALT];
        $secureHash = md5(http_build_query($query));

        if ($secureHash != $signature || !strlen($phone)) {
            return $this->outputJSON(result(false, ['result' => []]));
        }

        $currDate = \DateTimeEx::create()->toIsoString(FALSE);
        $baseStatus = [PaymentMapper::PAYMENT_UNPAID,
                PaymentMapper::PAYMENT_PROCESSING,
                PaymentMapper::PAYMENT_FAIL,
                PaymentMapper::PAYMENT_REFUND];
        $date = arrData($inputs, 'date', $currDate);
        $payments = PaymentMapper::makeInstance()->filterPhone($phone)->getEntities();
//        $payments = PaymentMapper::makeInstance()->filterPhone($phone)->filterCreatedDate($date)
//            ->filterStatus([PaymentMapper::PAYMENT_UNPAID,
//                PaymentMapper::PAYMENT_PROCESSING,
//                PaymentMapper::PAYMENT_FAIL,
//                PaymentMapper::PAYMENT_REFUND])->getEntities();
        $this->outputJSON(result(true, ['result' => $payments]));
    }

    function getPayment($id)
    {
        $inputs = $_GET;
        /* Kiểm tra xác thực chữ ký */
        $phone = arrData($inputs, 'phone');
        $signature = arrData($inputs, 'signature');
        $query = ['phone' => $phone,
            'salt' => PaymentMapper::PAYMENT_SALT];
        $secureHash = md5(http_build_query($query));

        if ($secureHash != $signature || !strlen($phone)) {
            return $this->outputJSON(result(false, ['result' => []]));
        }

        $payment = PaymentMapper::makeInstance()->filterID($id)->getEntities();
        $this->outputJSON(result(true, ['result' => $payment]));
    }

    function getPaymentConfig($configs, $provider, $name)
    {
        if (!$configs || !count($configs)) return "";

        $ret = "";
        foreach ($configs as $idx => $conf) {
            if (arrData($conf, 'provider') != $provider) continue;

            $pConf = arrData($conf, 'config', []);
            $ret = arrData($pConf, $name);
        }

        return $ret;

    }
}
