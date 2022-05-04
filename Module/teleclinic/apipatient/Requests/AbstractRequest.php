<?php

namespace Teleclinic\ApiPatient\Requests;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

abstract class AbstractRequest
{

    protected $rules;
    protected $valid = false;
    protected $input = [];
    /**
     * @var NestedValidationException $validationException
     */
    protected $validationException;

    /**
     * @param array $input
     * @return \Exception|NestedValidationException|void
     */
    abstract public function validate(array $input);


    /**
     * @param Validator $rules
     * @param array $input
     * @return $this
     */
    public function assert(Validator $rules, array $input)
    {
        try {
            $this->rules = $rules;
            $this->input = $input;
            $rules->assert($input);
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


}