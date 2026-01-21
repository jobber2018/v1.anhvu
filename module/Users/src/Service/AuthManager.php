<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 6:04 PM
 *
 */

namespace Users\Service;

use Sulde\Service\Common\Common;
use Users\Entity\User;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\Session;
use Zend\Session\Storage\ArrayStorage;
use Zend\Session\Validator\HttpUserAgent;

use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\SessionManager;

class AuthManager{

    public $authenticationService;
    private $sessionManager;
    private $config;

    public function __construct($authenticationService,$sessionManager,$config){
        $this->authenticationService = $authenticationService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    /**
     * @param $mobile_or_email
     * @param $password
     * @param $rememberMe
     * @return mixed
     * @throws \Exception
     */
    public function login($mobile_or_email, $password, $rememberMe){

        if($this->authenticationService->hasIdentity()){
            return new Result(
                Result::SUCCESS,
                $this->authenticationService->getIdentity(),
//                $_SESSION['userInfo'],
                ['Bạn đã đăng nhập']
            );
        }

        $authAdapter = $this->authenticationService->getAdapter();

        if(!Common::verifyEmail($mobile_or_email)){
            $mobile = Common::verifyMobile($mobile_or_email);
            $authAdapter->setMobile($mobile);
        }else{
            $authAdapter->setEmail($mobile_or_email);
        }

        $authAdapter->setPassword($password);

        $result = $this->authenticationService->authenticate();

        if($result->getCode() == Result::SUCCESS && $rememberMe){
            $this->sessionManager->rememberMe(86400*30);

        }

        return $result;
    }


    /**
     * @throws \Exception
     */
    public function logout(){
        if($this->authenticationService->hasIdentity()){
            $this->authenticationService->clearIdentity();
            session_unset();
            session_destroy();
//            $_SESSION['userInfo']=null;
        }
        else{
            throw new \Exception('Bạn chưa đăng nhập');
        }
    }

    public function filterAccess($controllerName, $actionName){
        if(isset($this->config['controllers'][$controllerName])){
            $controllers = $this->config['controllers'][$controllerName];
            foreach($controllers as $controller){
                $listAction = $controller['actions'];
                $allow = $controller['allow'];
                if(in_array($actionName, $listAction)){
                    if($allow=="all"){
                        return true; //được phép
                    }
                    elseif($allow=="limit" && $this->authenticationService->hasIdentity()){
                        return true;
                    }
                    else return false;
                }
            }
        }
        return true;
    }
}
