<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-08
 * Time: 13:36
 */

namespace Sulde\Service;


use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class SuldeFrontController extends AbstractActionController
{
    protected $userInfo;

    public function onDispatch(MvcEvent $e)
    {
        $authManager = $e->getApplication()->getServiceManager()->get(AuthenticationService::class);
        if ($authManager->hasIdentity()) {
            $this->userInfo = $authManager->getIdentity();
            $e->getViewModel()->setVariable('authIdentity', $authManager->getIdentity());
        }
        //Call default dispatch function
        $response = parent::onDispatch($e);

        return $response;
    }
}