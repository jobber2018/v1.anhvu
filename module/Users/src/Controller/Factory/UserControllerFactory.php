<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 12:03 PM
 *
 */


namespace Users\Controller\Factory;


use Interop\Container\ContainerInterface;
use Users\Controller\UserController;
use Users\Service\UserManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        return new UserController($entityManager, $userManager);
    }
}