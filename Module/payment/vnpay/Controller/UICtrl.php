<?php


namespace Payment\VNPAY\Controller;


use Company\Auth\Auth;
use Company\MVC\Json;
use Company\MVC\Layout;
use Company\MVC\Module;
use Company\Setting\Model\SettingDataMapper;
use Payment\BASE\Model\PaymentMapper;
use Payment\VNPAY\UiLoader;

class UICtrl extends \Company\MVC\Controller
{
    /**
     * @var Layout
     */
    protected $layout;

    function init()
    {
        parent::init();
        $module = Module::getInstance('payment/vnpay');
        $this->layout = Layout::getLayout('admin');
        $this->layout->addJS($module->getBabelURL('autoload.json'));
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

    function vnpReturn()
    {
        $orderID = arrData($_GET, 'vnp_TxnRef');
        /* Lấy thông tin thanh toán */
        $payment = PaymentMapper::makeInstance()->filterOrderID($orderID)->getEntity();

        if (!$payment || !$payment->id) {
            header('Content-type: text/html; charset=utf-8');
            die("Thông tin thanh toán không hợp lệ");
        }

        $siteConfig = SettingDataMapper::makeInstance()->getSetting($payment->siteID, "SiteConfig");
        $conf = !Json::decode($siteConfig) ? [] : Json::decode($siteConfig);
        $paymentConf = arrData($conf, 'payments', []);

        $vnp_TmnCode = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_TmnCode');
        $vnp_HashSecret = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_HashSecret');
        $vnp_PhoneSupport = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_phoneSuport');
        $vnp_cartUrl = arrData($conf, 'paymentCartUrl');

        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $rs = [];
        if ($secureHash == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                $rs = result(true, "Thanh toán thành công");
            } else {
                $rs = result(false, "Thanh toán không thành công");
            }
        } else {
            $rs = result(false, "Chữ ký không hợp lệ");
        }

        $pageData = ['vnp_TxnRef' => arrData($_GET, 'vnp_TxnRef'),
            'vnp_Amount' => arrData($_GET, 'vnp_Amount'),
            'vnp_OrderInfo' => arrData($_GET, 'vnp_OrderInfo'),
            'vnp_ResponseCode' => arrData($_GET, 'vnp_ResponseCode'),
            'vnp_TransactionNo' => arrData($_GET, 'vnp_TransactionNo'),
            'vnp_BankCode' => arrData($_GET, 'vnp_BankCode'),
            'vnp_PayDate' => arrData($_GET, 'vnp_PayDate'),
            'vnp_Result' => $rs,
            'vnp_PhoneSupport' => $vnp_PhoneSupport,
            'paymentCartUrl' => $vnp_cartUrl,
            'userPhone' => $payment->userPhone];

        $module = Module::getInstance('payment/vnpay');
        $this->layout->addCss($module->getPublicURL() . '/css/vnpay.css')->addJs($module->getPublicURL() . '/js/md5.js')
            ->setTitle("VNPAY")->renderReact('VnpayReturn', $pageData);
    }
}
