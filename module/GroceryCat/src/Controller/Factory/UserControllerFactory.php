<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace GroceryCat\Controller\Factory;


use GroceryCat\Controller\IndexController;
use GroceryCat\Controller\UserController;
use GroceryCat\Service\GroceryCatManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $amenitiesManager = $container->get(GroceryCatManager::class);
        return new UserController($entityManager, $amenitiesManager);
    }
}