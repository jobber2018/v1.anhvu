<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/18/19 8:07 PM
 *
 */

namespace Map\Controller;


use Product\Service\ProductManager;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\SuldeFrontController;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function indexAction()
    {
        return new ViewModel();
//        return $this->redirect()->toRoute('product-front');

    }

}