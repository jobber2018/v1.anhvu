<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 3:17 PM
 *
 */



namespace Users\Service\Factory;


use Interop\Container\ContainerInterface;
use Users\Service\AuthAdapter;
use Users\Service\AuthStorage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;

class AuthenticationServiceFactory implements FactoryInterface{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sessionManager = $container->get(SessionManager::class);
        $authStorage = new Session("Zend_Auth",'session',$sessionManager);

//        $authStorage = $container->get(AuthStorage::class);

        $authAdapter = $container->get(AuthAdapter::class);

        return new AuthenticationService($authStorage, $authAdapter);
    }
}