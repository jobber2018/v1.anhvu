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
use Report\Entity\Report;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\SessionManager;

class AccountingDiaryManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_id
     * @return AccountingDiary
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(AccountingDiary::class)->find($p_id);
    }
    /**
     * @return AccountingDiary
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('ad')
            ->from(AccountingDiary::class, 'ad')
            ->orderBy('ad.date','DESC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_fromDate
     * @param $p_toDate
     * @param $p_op
     * @return AccountingDiary
     */
    public function getByDate($p_fromDate,$p_toDate, $p_op=0)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('ad')
            ->from(AccountingDiary::class, 'ad')
            ->where("ad.tk IN (:tks)")
            ->andWhere("ad.op =:op")
            ->andWhere("DATE_FORMAT(ad.date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(ad.date,'%Y-%m-%d') <=:toDate")
            ->setParameters(array(
                'tks' => ConfigManager::getAccountFinance(),
                'op'=>$p_op,
                'fromDate'=>$p_fromDate,
                'toDate'=>$p_toDate
            ));
        return $queryBuilder->getQuery()->getResult();
    }
}