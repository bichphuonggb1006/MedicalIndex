<?php

namespace Teleclinic\ApiPatient\Repositories;

use Company\Entity\Entity;
use Teleclinic\ApiPatient\Model\ApiPatientPatientAttrMapper;
use Teleclinic\ApiPatient\Model\ApiPatientPatientMapper;
use Teleclinic\ApiPatient\Requests\PatientUpdateRequest;
use Teleclinic\Teleclinic\Model\PatientAttrMapper;
use Teleclinic\Teleclinic\Model\PatientAttrs\AbstractPatientAttr;
use Teleclinic\Teleclinic\Model\PatientMapper;

class ApiPatientPatientRepository
{

    public function store()
    {

    }

    public function update($patientEntity, array $input): \Result
    {
        /**
         * @var PatientMapper $patientEntity
         */
        $response = new \Result();
        $validator = (new PatientUpdateRequest($patientEntity))->validate($input);
        if (!$validator->isValid()) {
            return $response->setErrors('Params is invalid', $validator->getMessages(), 422);
        }

        ApiPatientPatientAttrMapper::makeInstance()->startTrans();
        $rsUpdateAttr = $this->updatePatientAttrs($patientEntity, $input);
        if (!$rsUpdateAttr->isOk()) {
            ApiPatientPatientAttrMapper::makeInstance()->rollbackTrans();;
            return $rsUpdateAttr;
        }
        ApiPatientPatientMapper::makeInstance()->filterID($patientEntity->id, $validator->getInputs());
        ApiPatientPatientAttrMapper::makeInstance()->completeTransOrFail();
        return $response->setSuccess();
    }

    /**
     * @param Entity $patientEntity
     * @param array $input
     * @return \Result
     * @throws \Exception
     */
    public function updatePatientAttrs(Entity $patientEntity, array $input): \Result
    {
        $response = new \Result();
        $attrErrors = [];
        foreach (ApiPatientPatientAttrMapper::$attrsKey as $attrKey => $attrClass) {
            if (is_null($attrClass) || trim($attrClass) == '')
                continue;
            //Nếu số điện thoại đã tồn tài=> ignore vì không được số điện thoại
            if ($attrKey == 'phone' && $patientEntity->phone) {
                continue;
            }
            if ($attrKey == 'email' && $patientEntity->email) {
                continue;
            }

            /**
             * @var AbstractPatientAttr $instanceAttr
             */
            $instanceAttr = new $attrClass;
            $instanceAttr->validate($input);
            if (!$instanceAttr->isValid()) {
                $attrErrors[$attrKey] = $instanceAttr->getMessages()[$attrKey] ?? 'System error';
            } else {
                if (isset($instanceAttr->toArray()[0]) && is_array($instanceAttr->toArray()[0])) {

                    foreach ($instanceAttr->toArray() as $attr) {
                        $attrExists = PatientAttrMapper::makeInstance()->filterPatientCode($patientEntity->code)->filterAttrCode($attr['attrCode'])->isExists();
                        if ($attrExists) {
                            PatientAttrMapper::makeInstance()
                                ->filterPatientCode($patientEntity->code)
                                ->filterAttrCode($attr['attrCode'])
                                ->update($attr);
                        } else {
                            PatientAttrMapper::makeInstance()->insert([
                                    'id' => uid(),
                                    'patientCode' => $patientEntity->code
                                ] + $attr);
                        }
                    }
                } else {
                    $attrExists = PatientAttrMapper::makeInstance()->filterPatientCode($patientEntity->code)->filterAttrCode($attrKey)->isExists();
                    if ($attrExists) {
                        PatientAttrMapper::makeInstance()
                            ->filterPatientCode($patientEntity->code)
                            ->filterAttrCode($attrKey)
                            ->update($instanceAttr->toArray());
                    } else {
                        PatientAttrMapper::makeInstance()->insert([
                                'id' => uid(),
                                'patientCode' => $patientEntity->code
                            ] + $instanceAttr->toArray());
                    }
                }


            }
        }
        if (count($attrErrors))
            return $response->setErrors('Param is invalid', $attrErrors, 422);
        return $response->setSuccess();
    }
}