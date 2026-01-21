<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 3:16 PM
 *
 */


namespace Users\Service\Factory;


use Interop\Container\ContainerInterface;
use Users\Service\AuthAdapter;
use Zend\ServiceManager\Factory\FactoryInterface;


class AuthAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new AuthAdapter($entityManager);
    }
}
{

}