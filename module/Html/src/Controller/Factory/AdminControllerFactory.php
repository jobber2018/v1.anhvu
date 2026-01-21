<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Html\Controller\Factory;


use Interop\Container\ContainerInterface;
use Html\Controller\AdminController;
use Html\Service\HtmlManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class AdminControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $htmlManager = $container->get(HtmlManager::class);
        return new AdminController($entityManager, $htmlManager);
    }
}