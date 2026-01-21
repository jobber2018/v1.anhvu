<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Report\Service;


use Doctrine\ORM\Query;
use Report\Entity\AccountingDiary;
use Report\Entity\CostRevenue;
use Report\Entity\Report;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\SessionManager;

class CostRevenueManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_fromDate
     * @param $p_toDate
     * @return CostRevenue
     */
    public function getByDate($p_fromDate,$p_toDate)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('cr')
            ->from(CostRevenue::class, 'cr')
            ->where("1=1")
            ->andWhere("DATE_FORMAT(cr.date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(cr.date,'%Y-%m-%d') <=:toDate")
            ->orderBy('cr.date', 'DESC')
            ->setParameters(array(
                'fromDate'=>$p_fromDate,
                'toDate'=>$p_toDate
            ));
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_date
     * @return CostRevenue
     */
    public function getDate($p_date)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('cr')
            ->from(CostRevenue::class, 'cr')
            ->where("DATE_FORMAT(cr.date,'%Y-%m-%d') >=:current_date")
            ->setParameters(array(
                'current_date'=>$p_date,
            ));
        return $queryBuilder->getQuery()->getResult();
    }
}