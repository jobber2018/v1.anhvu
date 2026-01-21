<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Report\Controller;


use Report\Service\ReportManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
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
        return new ViewModel();
    }
}