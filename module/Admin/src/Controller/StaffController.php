<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-15
 * Time: 15:55
 */

namespace Admin\Controller;


use Admin\Service\AdminManager;
use Doctrine\ORM\EntityManager;
use Product\Service\ProductManager;
use Sell\Entity\SellOrder;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\SuldeAdminController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class StaffController extends SuldeAdminController
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $adminManager = new AdminManager($this->entityManager);
        $adminManager->getActivityDateNow();
        /*foreach ($productNorm as $productItem){
            $nameSupplier='';
            $lastInputPrice=$productItem->getLastInputPrice()->getPrice();

            if($lastInputPrice){
                $boxUnit=$productItem->getLastInputPrice()->getBoxUnit();
                $advPrice = $lastInputPrice/$boxUnit;
                $productItem->setAveragePrice($advPrice);
                $this->entityManager->flush();
            }

        }*/

        return new ViewModel(['userInfo'=>$this->userInfo]);
    }
}