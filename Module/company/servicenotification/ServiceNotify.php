<?php

namespace Company\ServiceNotification;

use Result;

class ServiceNotify
{

    protected $http;

    public function __construct()
    {
        $serviceNotificationConfig = app()->config['serviceNotification'];
        $this->http = new \GuzzleHttp\Client([
            'base_uri' => arrData($serviceNotificationConfig, 'base_uri'),
            'headers' => [
                'authorization' => 'Bearer ' . arrData($serviceNotificationConfig, 'access_key'),
                'secret_key' => arrData($serviceNotificationConfig, 'secret_key'),
                'content-type' => 'application/json'
            ]
        ]);

    }

    /**
     * @param $reference_id
     * @param string $body
     * @param array $to
     * @param $type
     * @return Result
     */
    public function sendSMSPatientActiveAccount($reference_id, string $body, array $to): Result
    {
        $bodyRequest = [
            "reference_id" => $reference_id,
            "type" => 'TYPE_NOTIFY_ACTIVE_PATIENT_ACCOUNT',
            "to" => $to,
            "body" => $body
        ];
        $response = $this->http->post('/api/create-notify-sms', [
            'body' => json_encode($bodyRequest),
        ]);
        $rs = new Result();
        if ($response->getStatusCode() !== 200) {
            return $rs->setErrors('Xảy ra lỗi', $response->getBody());
        }

        return $rs->setSuccess(['res_form_service_notify' => $response->getBody()]);
    }

    /**
     * @param $reference_id
     * @param string $body
     * @param array $to
     * @param $type
     * @return Result
     */
    public function sendEmailPatientActiveAccount($reference_id, string $body, array $to): Result
    {
        $bodyRequest = [
            "reference_id" => $reference_id,
            "type" => 'TYPE_NOTIFY_ACTIVE_PATIENT_ACCOUNT',
            "to" => $to,
            "body" => $body
        ];
        $response = $this->http->post('/api/create-notify-email', [
            'body' => json_encode($bodyRequest)
        ]);

        $rs = new Result();
        if ($response->getStatusCode() !== 200) {
            return $rs->setErrors('Xảy ra lỗi', $response->getBody());
        }
        return $rs->setSuccess(['rs' => $response->getBody()]);
    }

    /**
     * @param $reference_id
     * @param string $body
     * @param array $to
     * @param $type
     * @return Result
     */
    public function sendSMS($reference_id, string $body, array $to, $type = "TYPE_NOTIFY_ACTIVE_PATIENT_ACCOUNT"): \Result
    {
        $bodyRequest = [
            "reference_id" => $reference_id,
            "type" => $type,
            "to" => $to,
            "body" => $body
        ];
        $response = $this->http->post('/api/create-notify-sms', [
            'body' => json_encode($bodyRequest)
        ]);

        $rs = new \Result();
        if ($response->getStatusCode() !== 200) {
            return $rs->setErrors('Xảy ra lỗi', $response->getBody());
        }
        return $rs->setSuccess(['rs' => $response->getBody()]);
    }
}