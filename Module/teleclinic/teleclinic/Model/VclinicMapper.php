<?php

namespace Teleclinic\Teleclinic\Model;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\SQL\AnyMapper;
use Company\SQL\Mapper;
use Company\User\Model\DepartmentMapper;
use Company\User\Model\UserMapper;

class VclinicMapper extends Mapper
{
    protected $autoloadDep;
    protected $autoloadService;
    protected $autoloadUser;

    function __construct()
    {
        parent::__construct();
        $this->orderBy('depID, sort');
    }

    function tableName()
    {
        return 'teleclinic_vclinic';
    }

    function tableAlias()
    {
        return 'vclinic';
    }

    function autoloadDep()
    {
        $this->autoloadDep = true;
        return $this;
    }

    function autoloadService()
    {
        $this->autoloadService = true;
        return $this;
    }

    function autoloadUser()
    {
        $this->autoloadUser = true;
        return $this;
    }

    function makeEntity($rawData)
    {
        $entity = parent::makeEntity($rawData);
        if ($entity->videoCall && is_string($entity->videoCall)) $entity->videoCall = json_decode($entity->videoCall, true);
        if ($this->autoloadDep) $entity->department = DepartmentMapper::makeInstance()->filterID($entity->depID)
            ->getEntity();
        if (!$entity->sort) $entity->sort = 0;
        if ($this->autoloadService && $entity->id) {
            $entity->services = ServiceListMapper::makeInstance()->filterClinicID($entity->id)->autoloadDirs()
                ->getEntities();
        }
        if ($entity->id && $this->autoloadUser) {
            $userIds = AnyMapper::makeInstance()->from('teleclinic_vclinic_user')->where('clinicID=' . $entity->id)
                ->select('userID')->getCol();
            if (empty($userIds)) {
                $userIds = ['no user found for clinic=' . $entity->id];
            }
            $entity->users = UserMapper::makeInstance()->filterID($userIds)->getEntities();
        }

        if ($entity->schedule && is_string($entity->schedule)) $entity->schedule = json_decode($entity->schedule, true);
        return $entity;
    }

    function filterNotDeleted()
    {
        $this->where('deletedAt is null', __FUNCTION__);
        return $this;
    }

    function filterUserId($userID)
    {
        $clinicIDs = AnyMapper::makeInstance()->from('teleclinic_vclinic_user')->where('userID=?')
            ->setParamWhere($userID)->select('clinicID')->getCol();
        if (empty($clinicIDs)) $clinicIDs = ['no clinic found with user=' . $userID];
        $this->filterID($clinicIDs);
        return $this;
    }

    function updateClinic($id, $input)
    {
        $update = [];
        // Chỉ TH fullcontrol hoặc có quyển QuanLyLichTruc mới đc update tt khác
        $required = ['name',
            'siteID',
            'depID'];
        foreach ($required as $field) {
            if (!arrData($input, $field)) throw new BadRequestException("Missing required field: " . implode(', ', $required));
        }

        if (isset($input['videoCall']) && is_array($input['videoCall'])) $input['videoCall'] = json_encode($input['videoCall']);

        $allowedFields = ['name',
            'siteID',
            'depID',
            'videoCall',
            'sort',
            'patientPerHour'];
        foreach ($allowedFields as $field) {
            $update[$field] = arrData($input, $field);
        }

        if (isset($input['schedule'])) {
            $update['schedule'] = json_encode((object)$input['schedule']);
        } else {
            $update['schedule'] = "{}";
        }

        $this->startTrans();
        if ($id) {
            $this->filterID($id)->update($update);
        } else {
            $this->insert($update);
            $id = $this->db->insert_Id();
        }

        $clinicServiceMap = arrData($input, 'services');
        AnyMapper::makeInstance()->from('teleclinic_vclinic_service')->where("clinicID=$id")->delete();
        if (is_array($clinicServiceMap)) {
            foreach ($clinicServiceMap as $service) {
                ServiceListMapper::makeInstance()->filterID($service['id'])->getEntityOrFail();
                AnyMapper::makeInstance()->from('teleclinic_vclinic_service')->insert(['clinicID' => $id,
                    'serviceID' => $service['id']]);
            }
        }

        //update phan quyen
        AnyMapper::makeInstance()->from('teleclinic_vclinic_user')->where('clinicID=' . intval($id))->delete();
        foreach (arrData($input, 'users', []) as $user) {
            AnyMapper::makeInstance()->from('teleclinic_vclinic_user')->insert(['clinicID' => $id,
                'userID' => $user['id']]);
        }
        $this->completeTransOrFail();
    }


    function filterDepID($depID)
    {
        if (!$depID) return $this;
        $this->where('depID=?', __FUNCTION__)->setParamWhere($depID, __FUNCTION__);
        return $this;
    }

    function filterSiteID($siteID)
    {
        $this->where("siteID=?", __FUNCTION__)->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    function filterLinkedService($serviceID)
    {
        $ids = AnyMapper::makeInstance()->from('teleclinic_vclinic_service')->where("serviceID=" . intval($serviceID))
            ->select('clinicID')->getCol();
        if (count($ids) == 0) $ids = ['no clinic found with service=' . $serviceID];
        $this->filterID($ids);
        return $this;
    }


}