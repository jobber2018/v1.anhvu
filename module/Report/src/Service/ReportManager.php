<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Report\Service;


use Api\Entity\ZaloApp;
use Doctrine\ORM\Query;
use Report\Entity\Report;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\SessionManager;

class ReportManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Report
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Report::class)->find($p_id);
    }

    /**
     * @return ZaloApp
     */
    public function getZaloInstall()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('za')
            ->from(ZaloApp::class, 'za')
            ->where('za.grocery >0')
            ->orderBy('za.created_date', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}