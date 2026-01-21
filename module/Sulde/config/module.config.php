<?php
/**
 * Copyright (c) 2019.
 * Created by   : TruongHM
 * Created date: 7/13/19 12:36 PM
 *
 */

namespace Sulde;

use Sulde\Plugin\Factory\TranslatorPluginFactory;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\I18n\Translator\TranslatorServiceFactory;
use Zend\I18n\View\Helper\Translate;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\SessionManager;

return [
    'router' => [
        // Open configuration for all possible routes
        'routes' => [
            // Define a new route called "blog"
            'sulde' => [
                // Define a "literal" route type:
                'type' => Segment::class,
                // Configure the route itself
                'options' => [
                    // Listen to "/blog" as uri:
                    'route' => '/sulde',
                    // Define default controller and action to be called when
                    // this route is matched
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'not-authorized' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/not-authorized.html',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'notAuthorized',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'layoutAdmin'   => __DIR__ . '/../view/layout/layoutAdmin.phtml',
            'layoutStaff'   => __DIR__ . '/../view/layout/layoutStaff.phtml',
            'layoutUserAdmin'   => __DIR__ . '/../view/layout/layoutUserAdmin.phtml',
            'layoutLogin'   => __DIR__ . '/../view/layout/layoutLogin.phtml',
            'paginator'     => __DIR__ . '/../view/sulde/partial/paginator.phtml',
            'error/404'     => __DIR__ . '/../view/error/404.phtml',
            'error/500'     => __DIR__ . '/../view/error/500.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'doctrine' => [
        'driver' => [
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            __NAMESPACE__.'_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    // register __NAMESPACE__.'_driver' for any entity under namespace `User\Entity`
                    __NAMESPACE__.'\Entity' => __NAMESPACE__.'_driver',
                ],
            ],
        ],
    ],
    'service_manager'=>[
        'factories' => [
//            TranslatorInterface::class => TranslatorServiceFactory::class,

        ]
    ]
];
?>