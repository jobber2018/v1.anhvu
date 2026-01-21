<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-14
 * Time: 10:55
 */

namespace Sulde\Service;


use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class SuldeUserController extends AbstractActionController
{
    const VERSION = '3.0.3-dev';
    protected $userInfo;

    public function onDispatch(MvcEvent $e)
    {
        $authManager = $e->getApplication()->getServiceManager()->get(AuthenticationService::class);
        if ($authManager->hasIdentity()) {
            $this->userInfo = $authManager->getIdentity();

            if($this->userInfo->getFullname()=='ThangNV'){
                session_destroy();
                return $this->redirect()->toRoute('user-login');
            }


            $this->layout()->setTemplate('layoutUserAdmin');
            $e->getViewModel()->setVariable('authIdentity', $authManager->getIdentity());
        }else{
            return $this->redirect()->toRoute('user-login');
        }

        //Call default dispatch function
        $response = parent::onDispatch($e);

        return $response;
    }
}