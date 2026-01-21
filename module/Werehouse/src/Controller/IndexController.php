<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Werehouse\Controller;


use Werehouse\Service\WerehouseManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
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