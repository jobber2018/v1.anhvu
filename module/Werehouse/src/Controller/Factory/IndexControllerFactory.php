<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Werehouse\Controller\Factory;


use Werehouse\Controller\IndexController;
use Werehouse\Service\WerehouseManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $werehouseManager = $container->get(WerehouseManager::class);
        return new IndexController($entityManager, $werehouseManager);
    }


}