<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Report\Controller\Factory;


use Report\Controller\IndexController;
use Report\Controller\UserController;
use Report\Service\ReportManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $reportManager = $container->get(ReportManager::class);
        return new UserController($entityManager, $reportManager);
    }
}