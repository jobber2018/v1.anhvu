<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Werehouse\Controller;


use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Exception;
use Supplier\Service\SupplierManager;
use Werehouse\Entity\Werehouse;
use Werehouse\Form\WerehouseForm;
use Werehouse\Service\WerehouseManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeFrontController;
use Sulde\Service\SuldeUserController;
use Users\Entity\User;
use Zend\Paginator\Paginator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $werehouseManager;

    public function __construct(EntityManager $entityManager, WerehouseManager $werehouseManager)
    {
        $this->entityManager = $entityManager;
        $this->werehouseManager = $werehouseManager;
    }

    public function indexAction()
    {

        return new ViewModel();
    }
}