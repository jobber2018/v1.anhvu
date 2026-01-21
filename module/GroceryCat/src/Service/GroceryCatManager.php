<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace GroceryCat\Service;


use GroceryCat\Entity\GroceryCat;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use GroceryCat\Entity\GroceryCatAnalytic;
use Hotels\Service\HotelManage;

class GroceryCatManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return GroceryCat
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(GroceryCat::class)->find($p_id);
    }

    /**
     * @return GroceryCat
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(GroceryCat::class, 'a')
            ->orderBy('a.day','ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    public function getList($p_userId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('gc')
            ->from(GroceryCat::class, 'gc')
            ->where('gc.user = :user_id')
            ->orderBy('gc.day', 'ASC')
            ->setParameter('user_id', $p_userId);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_catId
     * @param $p_fromDate
     * @param $p_toDate
     * @return GroceryCatAnalytic
     */
    public function getCatAnalyticFromToDateByRoute($p_catId,$p_fromDate,$p_toDate){
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('gca')
            ->from(GroceryCatAnalytic::class, 'gca')
            ->where('gca.groceryCat = :catId')
            ->andWhere("DATE_FORMAT(gca.created_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(gca.created_date,'%Y-%m-%d') <=:toDate")
            ->orderBy('gca.created_date', 'ASC')
            ->setParameters(array(
                'catId'=>$p_catId,
                'fromDate'=>$p_fromDate,
                'toDate'=>$p_toDate
            ));
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_fromDate
     * @param $p_toDate
     * @return GroceryCatAnalytic
     */
    public function getCatAnalyticFromToDate($p_fromDate,$p_toDate){
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('gca')
            ->from(GroceryCatAnalytic::class, 'gca')
            ->where("DATE_FORMAT(gca.created_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(gca.created_date,'%Y-%m-%d') <=:toDate")
            ->orderBy('gca.created_date', 'ASC')
            ->setParameters(array(
                'fromDate'=>$p_fromDate,
                'toDate'=>$p_toDate
            ));
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_uid
     * @param $p_fromDate
     * @param $p_toDate
     * @return GroceryCatAnalytic
     */
    public function getCatAnalyticFromToDateByUser($p_uid,$p_fromDate,$p_toDate){
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('gca')
            ->from(GroceryCatAnalytic::class, 'gca')
            ->where("gca.order_owner_id =:uid")
            ->andWhere("DATE_FORMAT(gca.created_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(gca.created_date,'%Y-%m-%d') <=:toDate")
            ->orderBy('gca.created_date', 'DESC')
            ->setParameters(array(
                'uid'=>$p_uid,
                'fromDate'=>$p_fromDate,
                'toDate'=>$p_toDate
            ));
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }
}