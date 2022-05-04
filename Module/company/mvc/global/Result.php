<?php

use Company\MVC\Json;

class Result
{
    protected $status = true;
    protected $data = [];
    protected $code = [];

    public function setSuccess($data = []): self
    {
        $this->status = true;
        $this->data = $data;
        $this->code = 200;
        return $this;
    }

    public function setErrors($message = 'System error', array $errors = [], $code = 500): self
    {
        $this->status = false;
        $this->data = [
            'message' => trim($message) == '' ? 'SYSTEM_ERROR' : $message,
            'errors' => $errors
        ];
        $this->code = $code;
        return $this;
    }

    public function toArray()
    {
        return [
            'status' => $this->status,
            'data' => $this->data,
            'code' => $this->code
        ];
    }

    public function isOk(): bool
    {
        return $this->status == true;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toJson()
    {
        $data = $this->data;
        $data = is_array($data) ? $data : (array)$data;
        return json_encode($data);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasKeyInData($name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getValueInDataByKey($key)
    {
        return $this->data[$key] ?? null;
    }
}