<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Report\Controller;


use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Exception;
use GroceryCat\Service\GroceryCatManager;
use Report\Entity\Report;
use Report\Form\ReportForm;
use Report\Service\ReportManager;
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
    private $reportManager;

    public function __construct(EntityManager $entityManager, ReportManager $reportManager)
    {
        $this->entityManager = $entityManager;
        $this->reportManager = $reportManager;
    }

    public function indexAction()
    {
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);
        $routeId = $this->params()->fromRoute('id',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        $userId= $this->userInfo->getId();
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCatList = $groceryCatManager->getList($userId);

        if($routeId){
            $groceryCat = $groceryCatManager->getById($routeId);
            $groceryCatAnalytic = $groceryCatManager->getCatAnalyticFromToDateByRoute($routeId, $fromDate, $toDate);
        }else{
            $groceryCatAnalytic=$groceryCatManager->getCatAnalyticFromToDateByUser($userId,$fromDate, $toDate);
        }

        return new ViewModel([
            "groceryCatList"=>$groceryCatList,
            "groceryCatAnalytic"=>$groceryCatAnalytic,
            "groceryCat"=>@$groceryCat
        ]);
    }
}