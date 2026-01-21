<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Sell\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Grocery\Entity\Grocery;
use Product\Entity\ProductPrice;
use Sell\Entity\DeliveryCar;
use Sell\Entity\Sell;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sell\Entity\SellAnalytic;
use Sell\Entity\SellOrder;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\Common\SessionManager;

class SellManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Sell
     */
    public function getById($p_id){
        $sell = $this->entityManager->getRepository(Sell::class)->find($p_id);
        return $sell;
    }

    /**
     * @param $p_id
     * @return SellOrder
     */
    public function getSellOrderById($p_id)
    {
        return $this->entityManager->getRepository(SellOrder::class)->find($p_id);
    }

    /**
     * @param $p_id
     * @return Sell
     */
    public function getSellById($p_id)
    {
        return $this->entityManager->getRepository(Sell::class)->find($p_id);
    }

    /**
     * @param $p_status
     * @return SellOrder
     */
    public function getSellOrder($p_status=-1)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so');
//            ->orderBy('so.pay_date','DESC');
        if($p_status==21 || $p_status==31){
            $queryBuilder->orderBy('so.delivered_date','DESC');
        }else{
            $queryBuilder->orderBy('so.created_date','DESC');
        }

        if($p_status==3){
            $queryBuilder->setFirstResult(0)
                ->setMaxResults(400)
                ->orderBy('so.pay_date','DESC');
        }

//        if($p_status >= 0){
            $queryBuilder->where("so.status =:status")
                ->setParameter('status', $p_status);
//        }
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return SellOrder
     */
    public function getSellOrderDelivery()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.status = 1")
            ->orWhere("so.status = 11")
            ->orWhere("so.status = 111")
            ->orWhere("so.status = 2")
            ->orWhere("so.status = -1")
            ->orWhere("so.status = -2")
            ->orderBy("so.delivered_date","DESC");
        return $queryBuilder->getQuery()->getResult();
    }
    public function getSellOrderDelivered($p_delivered_date)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');
        $configuration->addCustomStringFunction('UNIX_TIMESTAMP', 'DoctrineExtensions\Query\Mysql\UnixTimestamp');
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so.id,so.delivery_car,so.delivery_car_time,g.id as g_id,g.groceryName,g.lat,g.lng,UNIX_TIMESTAMP(so.delivered_date) as delivered_time,so.delivered_date')
            ->from(SellOrder::class, 'so')
            ->innerJoin(Grocery::class,'g','WITH','g.id=so.grocery')
            ->where("DATE_FORMAT(so.delivered_date,'%Y-%m-%d') =:delivered_date")
            ->orWhere("so.status = 2 and so.delivered_date is null")
            ->setParameter('delivered_date', $p_delivered_date)
            ->orderBy("so.delivered_date","ASC");
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_uid
     * @param $p_status
     * @return SellOrder
     */
    public function getSellOrderStatusById($p_uid,$p_status=-1)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.user=:uid")
            ->setParameter('uid', $p_uid)
            ->orderBy('so.id','DESC');
        if($p_status >= 0){
            $queryBuilder->andWhere("so.status =:status")
                ->setParameter('status', $p_status);
        }

//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_sellOrderOd
     * @param $p_status
     * @return SellOrder
     */
    public function updateStatus($p_sellOrderOd,$p_status=1)
    {
        $sellOrder = $this->getSellOrderById($p_sellOrderOd);
        $sellOrder->setStatus($p_status);
        $this->entityManager->flush();
        return $sellOrder;
    }

    /**
     * @param $p_fromDate
     * @param $p_toDate
     * @param $p_status
     * @return SellOrder
     */
    public function getSellOrderByDate($p_fromDate,$p_toDate, $p_status=3)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.status =:status")
            ->andWhere("DATE_FORMAT(so.pay_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(so.pay_date,'%Y-%m-%d') <=:toDate")
            ->setParameter('status', $p_status)
            ->setParameter('fromDate', $p_fromDate)
            ->setParameter('toDate', $p_toDate);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return SellOrder
     */
    public function getProductBuyOfDay($p_fromDate,$p_toDate)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.status >0")
            ->andWhere("DATE_FORMAT(so.confirmed_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(so.confirmed_date,'%Y-%m-%d') <=:toDate")
            ->setParameter('fromDate', $p_fromDate)
            ->setParameter('toDate', $p_toDate);;
        return $queryBuilder->getQuery()->getResult();

    }

    public function getTopGrocery($p_fromDate,$p_toDate, $p_status=3){

        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('g.id,g.groceryName as name, sum((pp.price*s.quantity)) as total_price, sum(pp.price*s.return) as total_return,sum(s.discount) as discount')
            ->from(SellOrder::class, 'so')
            ->innerJoin(Sell::class,'s','WITH','so.id = s.sellOrder')
            ->innerJoin(ProductPrice::class,'pp','WITH','s.price=pp.id')
            ->innerJoin(Grocery::class,'g','WITH','g.id=so.grocery')
            ->where("so.status =:status")
            ->andWhere("DATE_FORMAT(so.pay_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(so.pay_date,'%Y-%m-%d') <=:toDate")
            ->groupBy('g.id')
            ->setParameter('status', $p_status)
            ->setParameter('fromDate', $p_fromDate)
            ->setParameter('toDate', $p_toDate)
            ->setFirstResult(0)
            ->setMaxResults(20)
            ->orderBy('total_price', 'desc');
//echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_uid
     * @param $p_fromDate
     * @param $p_toDate
     * @param $p_status
     * @return SellOrder
     */
    public function getSellOrderByDateByUser($p_uid,$p_fromDate,$p_toDate, $p_status=0)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.user=:uid")
            ->andWhere("DATE_FORMAT(so.pay_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(so.pay_date,'%Y-%m-%d') <=:toDate")
            ->setParameter('uid', $p_uid)
            ->setParameter('fromDate', $p_fromDate)
            ->setParameter('toDate', $p_toDate);
        if($p_status>0){
            $queryBuilder->andWhere("so.status =:status")
                ->setParameter('status', $p_status);

        }
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_arrayId
     * @return SellOrder
     */
    public function getSellOrderIn($p_arrayId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
//            ->leftJoin('so.sell','s')
//            ->leftJoin('s.product','p')
            ->where("so.id IN (:ids)")
//            ->orderBy('p.name','DESC')
            ->setParameters(array('ids' => $p_arrayId));
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return SellAnalytic
     */
    public function getOrderAnalytic()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('sa')
            ->from(SellAnalytic::class, 'sa')
            ->orderBy('sa.diff','ASC');
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_zaloAppId
     * @return SellOrder
     */
    public function getOrderByZaloAppId($p_zaloAppId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.zalo_app_id=:zpId")
            ->setParameter('zpId', $p_zaloAppId)
            ->orderBy('so.id','DESC');

//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Lấy danh sách đơn ở trạng đã đóng gói của khách hàng bất kỳ
     * @param $p_groceryId
     * @return SellOrder
     */
    public function getSellOrderNeedMergeByGrocery($p_groceryId,$p_status=111)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.grocery=:grocery")
            ->andWhere("so.status=:status")//order o trang thai truyen vao (da dong goi, khach moi tao don, cho su ly)
            ->setParameter('grocery', $p_groceryId)
            ->setParameter('status', $p_status)
            ->orderBy('so.id','ASC');

//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_keyword
     * @param $p_status
     * @param $p_start
     * @param $p_length
     * @return Paginator
     */
    public function getOrderPaginator($p_keyword,$p_status,$p_start,$p_length)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->innerJoin(Grocery::class,'g','WITH','g.id=so.grocery')
            ->where('so.status = :status')
            ->setFirstResult($p_start)
            ->setMaxResults($p_length)
            ->setParameter('status', $p_status)
            ->orderBy('so.pay_date','DESC');

        if($p_keyword) {
            $queryBuilder->andWhere('p.name LIKE :name OR p.code LIKE :barcode')
                ->setParameter('name', '%'.$p_keyword.'%')
                ->setParameter('barcode', '%'.$p_keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @param $p_userId
     * @param $p_currentDate
     * @return DeliveryCar
     */
    public function getUserByLicensePlate($p_userId, $p_currentDate)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('dc')
            ->from(DeliveryCar::class, 'dc')
            ->where('dc.user = :userId')
            ->andWhere("DATE_FORMAT(dc.created_date,'%Y-%m-%d') =:date")
            ->setParameter('userId', $p_userId)
            ->setParameter('date', $p_currentDate);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_currentDate
     * @return DeliveryCar
     */
    public function getCarDeliveryByDate($p_currentDate)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('dc')
            ->from(DeliveryCar::class, 'dc')
            ->where("DATE_FORMAT(dc.created_date,'%Y-%m-%d') =:date")
            ->setParameter('date', $p_currentDate);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Lấy tất cả các đơn được gán cho xe trừ các đơn ở trạng thái đã giao, chưa thanh toán, đã thanh toán
     * @param $p_carLicense
     * @return SellOrder
     */
    public function getSellOrderByCar($p_carLicense)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.delivery_car =:carLicense")
            ->andWhere("so.status NOT IN (0,3, 31, 21)")
            ->setParameter('carLicense', $p_carLicense);
        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * get trang thai don hang truyen vao
     * @param $p_arrayStatus
     * @return SellOrder
     */
    public function getSellOrderViaStatus($p_arrayStatus)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('so')
            ->from(SellOrder::class, 'so')
            ->where("so.status IN (:status)")
            ->setParameters(array('status' => $p_arrayStatus))
            ->orderBy('so.created_date','DESC');
        return $queryBuilder->getQuery()->getResult();
    }
}