<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace GroceryCat\Controller;

use GroceryCat\Service\GroceryCatManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeAdminController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $groceryCatManager;

    public function __construct(EntityManager $entityManager, groceryCatCatManager $groceryCatManager)
    {
        $this->entityManager = $entityManager;
        $this->groceryCatManager = $groceryCatManager;
    }


    /**
     * @return ViewModel
     */
    public function indexAction(){

        return new ViewModel([
            'title'=>'Title'
        ]);
    }

}