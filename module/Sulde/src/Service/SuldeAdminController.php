<?php
/**
 * Copyright (c) 2019.
 * Created by   : TruongHM
 * Created date: 7/13/19 12:38 PM
 *
 */


namespace Sulde\Service;


use Sulde\Controller\IndexController;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class SuldeAdminController extends AbstractActionController
{
    const VERSION = '3.0.3-dev';
    protected $userInfo;

    public function onDispatch(MvcEvent $e)
    {
        $authManager = $e->getApplication()->getServiceManager()->get(AuthenticationService::class);
        if ($authManager->hasIdentity()) {

            $this->userInfo = $authManager->getIdentity();

            $routeMatch = $e->getRouteMatch();
            $controller = $routeMatch->getParam('controller');
            $action = $routeMatch->getParam('action');

//            $controller = $e->getTarget();
            // Get fully qualified class name of the controller.
//            $controllerClass = get_class($controller);
            // Get module name of the controller.
//            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));

            $isPermission = Common::isPermission($controller, $action, $authManager->getIdentity()->getPrivileges());

            if($isPermission){
//                $this->layout()->setTemplate('layoutAdmin');
//                $e->getViewModel()->setVariable('authIdentity', $authManager->getIdentity());
                $role = $this->userInfo->getRole();
                if($role=='admin')
                    $this->layout()->setTemplate('layoutAdmin');
                elseif($role=='staff')
                    $this->layout()->setTemplate('layoutStaff');

                $e->getViewModel()->setVariable('authIdentity', $authManager->getIdentity());
            }else{
                return $this->redirect()->toRoute('not-authorized');
            }
        }else{
            return $this->redirect()->toRoute('login');
        }
        //Call default dispatch function
        $response = parent::onDispatch($e);

        return $response;

    }
}