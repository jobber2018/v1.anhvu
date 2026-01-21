<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Html\Controller;

use Html\Service\HtmlManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeFrontController;
use Sulde\Service\SuldeUserController;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $htmlManager;

    public function __construct(EntityManager $entityManager, HtmlManager $htmlManager)
    {
        $this->entityManager = $entityManager;
        $this->htmlManager = $htmlManager;
    }

    public function indexAction()
    {
        //return new ViewModel();
    }
}