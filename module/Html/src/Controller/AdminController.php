<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Html\Controller;



use Doctrine\ORM\EntityManager;
use Html\Service\HtmlManager;
use Sulde\Service\SuldeAdminController;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $htmlManager;

    public function __construct(EntityManager $entityManager, HtmlManager $htmlManager)
    {
        $this->entityManager = $entityManager;
        $this->htmlManager = $htmlManager;
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