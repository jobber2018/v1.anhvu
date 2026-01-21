<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace SellCheck\Service;


use Doctrine\ORM\Query;
use Grocery\Entity\Grocery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Grocery\Entity\GroceryInOut;
use GroceryCat\Entity\GroceryCat;
use Hotels\Service\HotelManage;
use Sulde\Service\Common\SessionManager;

class SellCheckManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
}