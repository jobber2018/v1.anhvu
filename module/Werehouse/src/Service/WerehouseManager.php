<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Werehouse\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\Entity\Product;
use Werehouse\Entity\Werehouse;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\SessionManager;
use Werehouse\Entity\WerehouseCheck;
use Werehouse\Entity\WerehouseOrder;
use Werehouse\Entity\WerehouseScan;
use Werehouse\Entity\WerehouseSheet;

class WerehouseManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Werehouse
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Werehouse::class)->find($p_id);
    }

    public function getAllOrder()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('w')
            ->from(WerehouseOrder::class, 'w')
            ->setFirstResult(0)
            ->setMaxResults(100)
            ->orderBy('w.created_date', 'DESC');
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }
    /**
     * @param $p_id
     * @return WerehouseOrder
     */
    public function getOrderById($p_id)
    {
        return $this->entityManager->getRepository(WerehouseOrder::class)->find($p_id);
    }

    /**
     * @param $p_fromDate
     * @param $p_toDate
     * @param $p_status
     * @return WerehouseOrder
     */
    public function getOrderByDate($p_fromDate,$p_toDate,$p_status=1)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('wo')
            ->from(WerehouseOrder::class, 'wo')
            ->where("wo.status =:status")
            ->andWhere("DATE_FORMAT(wo.created_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(wo.created_date,'%Y-%m-%d') <=:toDate")
            ->setParameter('status', $p_status)
            ->setParameter('fromDate', $p_fromDate)
            ->setParameter('toDate', $p_toDate);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_sheetID
     * @return WerehouseSheet
     */
    public function getSheetById($p_sheetID)
    {
        return $this->entityManager->getRepository(WerehouseSheet::class)->find($p_sheetID);
    }

    /**
     * @param $p_cId
     * @return WerehouseCheck
     */
    public function getCheckById($p_cId)
    {
        return $this->entityManager->getRepository(WerehouseCheck::class)->find($p_cId);
    }

    /**
     * @return WerehouseSheet
     */
    public function getAllSheet()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('ws')
            ->from(WerehouseSheet::class, 'ws')
            ->orderBy('ws.created_date', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_uid
     * @return WerehouseSheet
     */
    public function getSheetByIdAndDateNow($p_uid)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('ws')
            ->from(WerehouseSheet::class, 'ws')
            ->where("ws.user =:uid")
            ->andWhere("DATE_FORMAT(ws.created_date,'%Y-%m-%d') =:sDate")
            ->setParameter('uid', $p_uid)
            ->setParameter('sDate', date("Y-m-d"));
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param mixed $p_keyword
     * @param mixed $p_length
     * @param mixed $p_start
     * @return Paginator
     */
    public function searchPurchaseScan($p_keyword, $p_length, $p_start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('ws')
            ->from(WerehouseScan::class, 'ws')
            ->innerJoin('ws.supplier', 's')
            ->where('1 = 1')
            ->setFirstResult($p_start)
            ->setMaxResults($p_length)
            ->orderBy('ws.id', 'DESC');

        if($p_keyword) {
            $queryBuilder->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$p_keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @param mixed $pScanId
     * @return WerehouseScan
     */
    public function getPurchaseScanById($pScanId)
    {
        return $this->entityManager->getRepository(WerehouseScan::class)->find($pScanId);
    }
}