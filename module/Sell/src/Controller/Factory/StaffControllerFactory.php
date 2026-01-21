<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Sell\Controller\Factory;

use Interop\Container\ContainerInterface;
use Sell\Controller\StaffController;
use Sell\Service\SellManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class StaffControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $sellManager = $container->get(SellManager::class);
        return new StaffController($entityManager, $sellManager);
    }
}