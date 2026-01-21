<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 11:58 AM
 *
 */



namespace Users\Service;


use DateTime;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Users\Entity\Roles;
use Users\Entity\User;
use Zend\Crypt\Password\Bcrypt;

class RolesManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @return Roles
     */
    public function getAll(){
        $roles = $this->entityManager->getRepository(Roles::class)->findAll();
        return $roles;
    }

    /**
     * @param $roleId
     * @return Roles
     */
    public function getByID($roleId){
        $role = $this->entityManager->getRepository(Roles::class)->find($roleId);
        return $role;
    }

    /**
     * @param $roleCode
     * @return User
     */
    public function getUser($roleCode)
    {
        return $this->entityManager->getRepository(User::class)->findBy(array('role' => $roleCode));
    }

}