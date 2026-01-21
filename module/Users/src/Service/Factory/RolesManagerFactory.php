<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 11:46 AM
 *
 */

namespace Users\Service\Factory;


use Interop\Container\ContainerInterface;
use Users\Service\RolesManager;
use Users\Service\UserManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class RolesManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new RolesManager($entityManager);
    }
}