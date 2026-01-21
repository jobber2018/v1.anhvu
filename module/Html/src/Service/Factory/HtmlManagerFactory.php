<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:48 AM
 *
 */

namespace Html\Service\Factory;


use Interop\Container\ContainerInterface;
use Html\Service\HtmlManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class HtmlManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new HtmlManager($entityManager);
    }
}