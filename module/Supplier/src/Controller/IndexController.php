<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Supplier\Controller;


use Supplier\Service\SupplierManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
{

    private $entityManager;
    private $supplierManager;

    public function __construct(EntityManager $entityManager, SupplierManager $supplierManager)
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
    }
    public function indexAction()
    {
        return new ViewModel();
    }
}