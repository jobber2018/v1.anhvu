<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace GroceryCat\Controller;


use GroceryCat\Service\GroceryCatManager;
use Doctrine\ORM\EntityManager;
use Hotels\Service\HotelManage;
use Sulde\Service\SuldeFrontController;
use Sulde\Service\SuldeUserController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $amenitiesManager;

    public function __construct(EntityManager $entityManager, GroceryCatManager $amenitiesManager)
    {
        $this->entityManager = $entityManager;
        $this->amenitiesManager = $amenitiesManager;
    }

    public function indexAction()
    {
        return new ViewModel();
    }
}