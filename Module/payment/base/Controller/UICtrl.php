<?php


namespace Payment\BASE\Controller;


use Company\Auth\Auth;
use Company\MVC\Json;
use Company\MVC\Layout;
use Company\MVC\Module;
use Company\Setting\Model\SettingDataMapper;
use Payment\BASE\Model\PaymentMapper;
use Payment\BASE\UiLoader;

class UICtrl extends \Company\MVC\Controller
{
    /**
     * @var Layout
     */
    protected $layout;

    function init()
    {
        parent::init();
        $module = Module::getInstance('payment/base');
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

    function cart()
    {
        $inputs = $_GET;
        /* Kiểm tra xác thực chữ ký */
        $phone = arrData($inputs, 'phone');
        $signature = arrData($inputs, 'signature');

        $query = ['phone' => $phone,
            'salt' => PaymentMapper::PAYMENT_SALT];
        $secureHash = md5(http_build_query($query));

        if ($secureHash != $signature || !strlen($phone)) {
            header('Content-type: text/html; charset=utf-8');
            die("Sai chữ ký");
        }

        $module = Module::getInstance('payment/base');
        $this->layout->addCss($module->getPublicURL() . '/css/payment.css')
            ->setTitle("Thanh toán")
            ->renderReact('Cart', ['phone' => $phone,
                'signature' => $signature]);
    }
}
