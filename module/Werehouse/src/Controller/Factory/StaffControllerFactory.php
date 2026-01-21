<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Werehouse\Controller\Factory;

use Interop\Container\ContainerInterface;
use Sell\Service\SellManager;
use Werehouse\Controller\AdminController;
use Werehouse\Controller\StaffController;
use Werehouse\Service\WerehouseManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class StaffControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $werehouseManager = $container->get(WerehouseManager::class);
        return new StaffController($entityManager, $werehouseManager);
    }
}