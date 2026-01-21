<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-14
 * Time: 10:57
 */

namespace Users\Controller\Factory;


use Interop\Container\ContainerInterface;
use Users\Controller\AdminController;
use Users\Controller\RolesController;
use Users\Service\RolesManager;
use Users\Service\UserManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class RolesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $rolesManager = $container->get(RolesManager::class);
        return new RolesController($entityManager, $rolesManager);
    }
}