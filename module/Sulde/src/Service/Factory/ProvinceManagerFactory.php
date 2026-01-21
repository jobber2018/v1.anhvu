<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/21/19 9:59 PM
 *
 */

namespace Sulder\Service\Factory;


use Interop\Container\ContainerInterface;
use Sulde\Service\ProvinceManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProvinceManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new ProvinceManager($entityManager);
    }


}