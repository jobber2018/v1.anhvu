<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-09-18
 * Time: 21:42
 */

namespace Map\Controller\Factory;


use Interop\Container\ContainerInterface;
use Map\Controller\IndexController;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new IndexController($entityManager);
    }
}