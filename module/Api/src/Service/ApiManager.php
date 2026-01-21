<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Api\Service;


use Api\Entity\ZaloApp;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sell\Service\SellManager;
use Sulde\Service\Common\SessionManager;

class ApiManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_zalo_id
     * @return ZaloApp
     */
    public function getByZaloId($p_zalo_id){
        return $this->entityManager->getRepository(ZaloApp::class)->findOneBy(array('zalo_id' => $p_zalo_id));
    }
    /**
     * @param $p_id
     * @return ZaloApp
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(ZaloApp::class)->find($p_id);
    }

}