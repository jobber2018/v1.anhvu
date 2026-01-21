<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Grocery\Service;


use Doctrine\ORM\Query;
use Grocery\Entity\Grocery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Grocery\Entity\GroceryInOut;
use GroceryCat\Entity\GroceryCat;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\SessionManager;

class GroceryManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Grocery
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Grocery::class)->find($p_id);
    }

    /**
     * @param $p_mobile
     * @return Grocery
     */
    public function getByMobile($p_mobile)
    {
        return $this->entityManager->getRepository(Grocery::class)->findOneBy(array('mobile' => $p_mobile));
    }

    /**
     * @param $p_active
     * @return Grocery
     */
    public function getAll($p_active=1)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Grocery::class, 'g')
            ->where('g.active = :active')
            ->setParameter('active', $p_active);
        return $queryBuilder->getQuery()->getResult();
    }

    public function getListByCat($p_groceryCatId,$p_active=1)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Grocery::class, 'g')
            ->where('g.groceryCat = :grocery_cat_id')
            ->andWhere('g.active = :active')
            ->setParameter('active', $p_active)
            ->setParameter('grocery_cat_id', $p_groceryCatId);
        return $queryBuilder->getQuery()->getResult();
    }
    public function getListByCatPosition($p_groceryCatId,$p_lat,$p_lng,$p_textSearch="",$p_active=1)
    {
        //danh sach cua hang gan vi tri nhat
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('ACOS', 'DoctrineExtensions\Query\Mysql\Acos');
        $configuration->addCustomStringFunction('SIN', 'DoctrineExtensions\Query\Mysql\Sin');
        $configuration->addCustomStringFunction('RADIANS', 'DoctrineExtensions\Query\Mysql\Radians');
        $configuration->addCustomStringFunction('COS', 'DoctrineExtensions\Query\Mysql\Cos');

        $distance = 'ACOS(SIN( RADIANS(g.lat) ) * SIN(RADIANS('.$p_lat.')) + COS( RADIANS(g.lat)) * COS( RADIANS('.$p_lat.')) * COS(RADIANS(g.lng) - RADIANS('.$p_lng.')) ) * 6371';

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('g.id,g.groceryName,g.ownerName,g.mobile,g.lat,g.lng,g.address,g.check_in_date,g.check_out_date,g.time_in_grocery, g.active, g.pay_total, '.$distance . 'as distance')
            ->from(Grocery::class, 'g')
            ->where('g.groceryCat = :grocery_cat_id')
            ->andWhere('g.active = :active')
            ->setParameter('active', $p_active)
            ->setParameter('grocery_cat_id', $p_groceryCatId)
            ->orderBy("distance", "ASC");

        if($p_textSearch) {
            $queryBuilder->andWhere('g.groceryName LIKE :name')
                ->setParameter('name', '%'.$p_textSearch.'%');
        }
        return $queryBuilder->getQuery()->getResult();
    }

    public function getByLocality($p_lat,$p_lng,$p_groceryId){
        //lay khoang cach tu p_lat,p_lng so voi mot groceryID
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('ACOS', 'DoctrineExtensions\Query\Mysql\Acos');
        $configuration->addCustomStringFunction('SIN', 'DoctrineExtensions\Query\Mysql\Sin');
        $configuration->addCustomStringFunction('RADIANS', 'DoctrineExtensions\Query\Mysql\Radians');
        $configuration->addCustomStringFunction('COS', 'DoctrineExtensions\Query\Mysql\Cos');

        $distance = 'ACOS(SIN( RADIANS(l.lat) ) * SIN(RADIANS('.$p_lat.')) + COS( RADIANS(l.lat)) * COS( RADIANS('.$p_lat.')) * COS(RADIANS(l.lng) - RADIANS('.$p_lng.')) ) * 6371';


        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('l.id,l.lat,l.lng,l.address, '.$distance . 'as distance')
            ->from(Grocery::class, 'l')
            ->where('l.id = :grocery_id')
            ->setParameter('grocery_id', $p_groceryId);
//        echo $queryBuilder->getQuery()->getSQL();

        return $queryBuilder->getQuery()->getSingleResult();

    }

    /**
     * @param $p_groceryId
     * @param $p_type
     * @return GroceryInOut
     */
    public function getCheckInOutLast($p_groceryId,$p_type='in')
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('gin')
            ->from(GroceryInOut::class, 'gin')
            ->where('gin.grocery = :grocery_id')
            ->andWhere('gin.type = :type')
            ->setParameter('grocery_id', $p_groceryId)
            ->setParameter('type', $p_type)
            ->orderBy('gin.id', 'DESC')
            ->setMaxResults(1)
            ->setFirstResult(0);
//        echo $queryBuilder->getQuery()->getSQL();
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $p_uid
     * @param $p_fDate
     * @param $p_tDate
     * @return GroceryInOut
     */
    public function getCheckInByUserAndDate($p_uid,$p_fDate,$p_tDate)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('gin')
            ->from(GroceryInOut::class, 'gin')
            ->where('gin.user = :uid')
            ->andWhere('gin.type=:type')
            ->andWhere("DATE_FORMAT(gin.created_date,'%Y-%m-%d') >=:fromDate")
            ->andWhere("DATE_FORMAT(gin.created_date,'%Y-%m-%d') <=:toDate")
            ->setParameter('uid', $p_uid)
            ->setParameter('type', 'in')
            ->setParameter('fromDate', $p_fDate)
            ->setParameter('toDate', $p_tDate)
            ->orderBy('gin.created_date', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_lat
     * @param $p_lng
     * @return Grocery
     */
    public function getGroceryLocation($p_lat, $p_lng)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('ACOS', 'DoctrineExtensions\Query\Mysql\Acos');
        $configuration->addCustomStringFunction('SIN', 'DoctrineExtensions\Query\Mysql\Sin');
        $configuration->addCustomStringFunction('RADIANS', 'DoctrineExtensions\Query\Mysql\Radians');
        $configuration->addCustomStringFunction('COS', 'DoctrineExtensions\Query\Mysql\Cos');

        $where = 'ACOS(SIN( RADIANS(g.lat) ) * SIN(RADIANS('.$p_lat.')) + COS( RADIANS(g.lat)) * COS( RADIANS('.$p_lat.')) * COS(RADIANS(g.lng) - RADIANS('.$p_lng.')) ) * 6371 < 1';

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(Grocery::class, 'g')
            ->where($where)
            ->andWhere('g.active = 1');
        return $queryBuilder->getQuery()->getResult();
    }
}