<?php

namespace Teleclinic\Teleclinic\Model\PatientAttrs;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

abstract class AbstractPatientAttr
{

    protected $attrCode;
    protected $attrName;
    protected $attrValue = null;
    protected $dData = null;
    protected $valid = false;
    protected $input = [];
    /**
     * @var Validator $rule
     */
    protected $rule;
    /**
     * @var NestedValidationException $validationException
     */
    protected $validationException;


    public function assert(Validator $rule, $input)
    {
        try {
            $this->input = $input;
            $this->attrValue = $input[$this->attrCode] ?? null;
            $this->rule = $rule;
            $this->rule->assert($input);

            $this->valid = true;
        } catch (NestedValidationException $exception) {
            $this->validationException = $exception;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return mixed|null
     */
    public function getInputs()
    {
        if ($this->valid)
            return $this->input;

        return $this->validationException->getParam('input') ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getMessages()
    {
        if ($this->valid)
            return null;

        return $this->validationException->getMessages() ?? null;
    }


    public function toArray(): array
    {
        if ($this->valid)
            return [
                'attrCode' => $this->attrCode,
                'attrName' => $this->attrName,
                'attrValue' => $this->attrValue,
                'dData' => $this->dData,
            ];
        return [];
    }

    abstract public function validate(array $input);
}