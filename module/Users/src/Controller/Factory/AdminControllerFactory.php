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
use Users\Service\UserManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class AdminControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        return new AdminController($entityManager, $userManager);
    }
}