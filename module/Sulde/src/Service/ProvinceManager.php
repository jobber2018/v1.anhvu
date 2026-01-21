<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/21/19 9:58 PM
 *
 */



namespace Sulde\Service;


use Sulde\Entity\Province;
use Users\Entity\User;
use Zend\Crypt\Password\Bcrypt;

class ProvinceManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $id
     * @return Province
     */
    public function getById($id){
        return $this->entityManager->getRepository(Province::class)->find($id);
    }

    public function getProvinceList(){
        return $this->entityManager->getRepository(Province::class)->findBy(array('active' => 1));
    }
}