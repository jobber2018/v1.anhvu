<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-08
 * Time: 13:37
 */

namespace Application\Controller\Factory;


use Application\Controller\IndexController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new IndexController($entityManager);
    }
}