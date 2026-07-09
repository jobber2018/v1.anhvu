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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Grocery\Entity\Grocery;
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
            ->orderBy('za.id', 'desc');

        return $queryBuilder->getQuery()->getResult();
    }

    public function getAccessApp($p_keyword, $p_length, $p_start)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select("za.id, CONCAT(za.zalo_id,'') as zalo_id,za.name,za.phone,za.avatar,DATE_FORMAT(za.created_date,'%Y-%m-%d %H:%s:%i') as created_date, DATE_FORMAT(za.access_date,'%Y-%m-%d %H:%s:%i') as access_date,g.id as grocery_id,g.groceryName,g.address")
            ->from(ZaloApp::class, 'za')
            ->leftJoin(Grocery::class,'g','WITH','g.id=za.grocery')
            ->setFirstResult($p_start)
            ->setMaxResults($p_length)
            ->orderBy('za.access_date', 'desc');

        if($p_keyword) {
            $queryBuilder->andWhere('g.groceryName LIKE :name OR za.phone LIKE :phone')
                ->setParameter('name', '%'.$p_keyword.'%')
                ->setParameter('phone', '%'.$p_keyword.'%');
        }
        return $queryBuilder->getQuery()->getResult();
    }
}