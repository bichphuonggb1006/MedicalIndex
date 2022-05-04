<?php

namespace Payment\VNPAY\Controller;

use Company\Exception\BadRequestException;
use Company\MVC\Controller;
use Company\MVC\Json;
use Company\Site\Model\SiteMapper;
use Payment\BASE\Model\PaymentMapper;
use Company\Setting\Model\SettingDataMapper;

class VNPayPaymentCtrl extends Controller
{
    protected function init()
    {
        parent::init();
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

    function vnpCreate($siteID)
    {
        $inputs = $this->input();
        $redirect = arrData($inputs, 'redirect', 0);
        $siteConfig = SettingDataMapper::makeInstance()->getSetting($siteID, "SiteConfig");
        $conf = !Json::decode($siteConfig) ? [] : Json::decode($siteConfig);
        $paymentConf = arrData($conf, 'payments', []);

        $vnp_TmnCode = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_TmnCode');
        $vnp_HashSecret = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_HashSecret');
        $vnp_Url = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_Url');
        $vnp_Returnurl = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_returnUrl');
        $vnp_apiUrl = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_apiUrl');
        $vnp_cartUrl = $this->getPaymentConfig($paymentConf, "VNPAY", 'vnp_cartUrl');
        $startTime = date("YmdHis");

        $vnp_TxnRef = arrData($inputs, 'orderID'); // Mã hồ sơ, duy nhất trên hệ thống
        $vnp_OrderInfo = arrData($inputs, 'paymentContent'); // Nội dung chuyển khoản Tên BN - Khám online
        $vnp_OrderType = 270001; // Mã loại hàng hóa (270001 = Đăng ký khám/chữa bệnh) theo tài liệu cung cấp
        $vnp_Amount = intval(arrData($inputs, 'amount', 0)) * 100; // Số tiền
        $vnp_Locale = "vn";
        $vnp_BankCode = ""; // Mã ngân hàng, hoặc rỗng
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

        /* Thông tin hóa đơn, khách hàng */
        $vnp_Bill_Mobile = arrData($inputs, 'userPhone'); // SĐT hóa đơn (Đang sd sđt đăng ký khám)
        $vnp_Bill_Email = arrData($inputs, 'userEmail'); // Email đăng ký (ko có)
        $fullName = trim(arrData($inputs, 'userName'));
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName), ' ');
        if (isset($fullName) && trim($fullName) != '') {
            $name = explode(' ', $fullName);
            $vnp_Bill_FirstName = array_shift($name);
            $vnp_Bill_LastName = array_pop($name);
        }
        $vnp_Bill_Address = arrData($inputs, 'userAddress');

        /* Thông tin hóa đơn điện tử*/
        $vnp_Inv_Phone = ""; // SĐT
        $vnp_Inv_Email = ""; // Email KH
        $vnp_Inv_Customer = ""; // Tên KH
        $vnp_Inv_Address = ""; // Địa chỉ
        $vnp_Inv_Company = ""; // Công ty
        $vnp_Inv_Taxcode = ""; // Mã số thuế
        $vnp_Inv_Type = ""; // Loại hóa đơn

        $inputData = array(// Version kết nối (Bắt buộc)
            "vnp_Version" => "2.1.0",
            // Mã KH do VNPAY cung cấp (Bắt buộc)
            "vnp_TmnCode" => $vnp_TmnCode,
            // Số tiền = Số tiền * 100 (Bắt buộc)
            "vnp_Amount" => $vnp_Amount,
            // Mã API sử dụng, mã cho giao dịch thanh toán là: pay. (Bắt buộc)
            "vnp_Command" => "pay",
            // Thời gian tạo bản ghi (Bắt buộc)
            "vnp_CreateDate" => date('YmdHis'),
            // Đơn vị tiền tệ. Hiện tại chỉ hỗ trợ VND (Bắt buộc)
            "vnp_CurrCode" => "VND",
            // Địa chỉ IP của khách hàng thực hiện giao dịch. Ví dụ: 13.160.92.202
            "vnp_IpAddr" => $vnp_IpAddr,
            // Ngôn ngữ giao diện hiển thị. Hiện tại hỗ trợ Tiếng Việt (vn), Tiếng Anh (en)
            "vnp_Locale" => $vnp_Locale,
            // Thông tin mô tả nội dung thanh toán (Tiếng Việt, không dấu (Bắt buộc)
            "vnp_OrderInfo" => $vnp_OrderInfo,
            // Mã loại hàng hóa (270001 = Đăng ký khám/chữa bệnh) theo tài liệu cung cấp (Tùy chọn)
            "vnp_OrderType" => $vnp_OrderType,
            // URL thông báo kết quả giao dịch khi Khách hàng kết thúc thanh toán. (Bắt buộc)
            "vnp_ReturnUrl" => $vnp_Returnurl,
            // Mã tham chiếu (mã hóa đơn) của giao dịch tại hệ thống của merchant. Mã này là duy nhất dùng để phân biệt các đơn hàng gửi sang VNPAY. Không được trùng lặp trong ngày (bắt buộc)
            "vnp_TxnRef" => $vnp_TxnRef,
            // Thời hạn thanh toán (đang để sau 15p)
            "vnp_ExpireDate" => $vnp_ExpireDate,
            // SĐT KH
            "vnp_Bill_Mobile" => $vnp_Bill_Mobile,
            // Email KH
            "vnp_Bill_Email" => $vnp_Bill_Email,
            // Họ KH
            "vnp_Bill_FirstName" => $vnp_Bill_FirstName,
            // Tên KH
            "vnp_Bill_LastName" => $vnp_Bill_LastName,
            // Địa chỉ KH
            "vnp_Bill_Address" => $vnp_Bill_Address,
            // SĐT hóa đơn đt
            "vnp_Inv_Phone" => $vnp_Inv_Phone,
            // Email hóa đơn đt
            "vnp_Inv_Email" => $vnp_Inv_Email,
            // Tên KH trên hóa đơn đt
            "vnp_Inv_Customer" => $vnp_Inv_Customer,
            // Địa chỉ Cty trên hóa đơn đt
            "vnp_Inv_Address" => $vnp_Inv_Address,
            // Tên Cty trên hóa đơn đt
            "vnp_Inv_Company" => $vnp_Inv_Company,
            // Mã số thuế trên hóa đơn đt
            "vnp_Inv_Taxcode" => $vnp_Inv_Taxcode,
            // Loại thanh toán
            "vnp_Inv_Type" => $vnp_Inv_Type);

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            // Mã ngân hàng
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        /* Kiểm tra các trường bắt buộc nhập */
        $notEmpties = ['vnp_Version',
            'vnp_TmnCode',
            'vnp_Amount',
            'vnp_Command',
            'vnp_CreateDate',
            'vnp_CurrCode',
            'vnp_IpAddr',
            'vnp_Locale',
            'vnp_OrderInfo',
            'vnp_ReturnUrl',
            'vnp_TxnRef'];

        $errors = [];
        foreach ($notEmpties as $field) {
            if (!isset($inputData[$field]) || !strlen(arrData($inputData, $field))) {
                $errors[] = $field . ",";
            }
        }

        if (count($errors)) {
            if (!$redirect) {
                $this->outputJSON(result(false, ['error' => "Thông tin " . rtrim(implode(",", $errors), ",") . " bị trống"]));
            } else {
                header('Content-type: text/html; charset=utf-8');
                die("Thông tin " . rtrim(implode(",", $errors), ",") . " bị trống");
            }
        }

        /* Loại bỏ các input rỗng . tránh lỗi sai chữ ký */
        $tmpInputs = [];
        foreach ($inputData as $key => $data) {
            if (!strlen($data)) continue;

            $tmpInputs[$key] = $data;
        }

        /* gán lại mảng input */
        $inputData = $tmpInputs;

        /* Bắt buộc sắp xếp input */
        ksort($inputData);

        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }

            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        if (!$redirect) {
            $this->outputJSON(result(true, ['code' => '00',
                'message' => 'success',
                'url' => $vnp_Url]));
        } else {
            header('Location: ' . $vnp_Url);
            die();
        }
    }

    function vnpIpn()
    {

    }
}
