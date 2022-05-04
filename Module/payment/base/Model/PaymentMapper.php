<?php

namespace Payment\BASE\Model;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\SQL\AnyMapper;
use Company\SQL\Mapper;
use Company\User\Model\DepartmentMapper;
use Company\User\Model\UserMapper;

class PaymentMapper extends Mapper
{

    const PAYMENT_PROCESSING = 'processing';
    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_REFUND = 'refund';
    const PAYMENT_FAIL = 'fail';
    const PAYMENT_SALT = 'sAAOIQkjwwiq__7273720'; // Mã bảo mật để xác thục 1 phần signature GET

    function __construct()
    {
        parent::__construct();
    }

    function tableName()
    {
        return 'payment_transaction';
    }

    function tableAlias()
    {
        return 'pay_tr';
    }

    function makeEntity($rawData)
    {
        $entity = parent::makeEntity($rawData);
        return $entity;
    }

    function filterOrderID($id)
    {
        if (strlen($id)) $this->where('orderID=?', __FUNCTION__)->setParamWhere($id, __FUNCTION__);
        return $this;
    }

    function filterPhone($phone)
    {
        if (strlen($phone)) $this->where("userPhone=?", __FUNCTION__)->setParamWhere($phone, __FUNCTION__);

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

    function filterStatus($status)
    {
        if (count($status) && is_array($status)) {
            $this->filterIn('`status`', $status);
            return $this;
        }

        if (strlen($status)) $this->where('status=?', __FUNCTION__)->setParamWhere($status, __FUNCTION__);
        return $this;
    }

    function newPayment($updateData)
    {
        /* validate */
        $notNulls = ['orderType',
            'orderID',
            'status',
            'userName',
            'amount',
            'payment',
            'paymentContent',
            'createdTime'];

        foreach ($notNulls as $field) {
            if (!strlen($updateData[$field])) throw new BadRequestException("missing required fields: $field");
        }

        if (!in_array($updateData['status'], [static::PAYMENT_PROCESSING,
            static::PAYMENT_UNPAID,
            static::PAYMENT_PAID,
            static::PAYMENT_REFUND,
            static::PAYMENT_FAIL])) throw new BadRequestException("invalid payment status");

        if (!is_numeric($updateData['amount'])) {
            throw new BadRequestException("Amount must be currency");
        }

        $this->startTrans();
        $this->insert($updateData);
        $id = $this->db->Insert_ID();
        $this->completeTransOrFail();
        return result(true, ['id' => $id]);
    }

    function generateUniqID($provider)
    {
        $year = date("Y");
        $endYear = substr($year, -2);
        $seq = strtoupper($provider) . $endYear;
        $uniqId = $this->uniqID($seq, "ID sinh dùng tạo mã hóa đơn");
        $orderID = $endYear . sprintf("%06d", $uniqId);
        return $orderID;
    }
}