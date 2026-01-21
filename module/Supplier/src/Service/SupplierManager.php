<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Supplier\Service;


use Doctrine\ORM\Query;
use Supplier\Entity\Supplier;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\SessionManager;

class SupplierManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Supplier
     */
    public function getById($p_id){
        $supplier = $this->entityManager->getRepository(Supplier::class)->find($p_id);
        return $supplier;
    }

    /**
     * @return Supplier
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('s')
            ->from(Supplier::class, 's')
            ->orderBy('s.created_date', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }
}