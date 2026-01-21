<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/24/19 10:57 AM
 *
 */


namespace Werehouse;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'werehouse-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/werehouse[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'werehouse-user' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user/werehouse[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'werehouse-staff' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/staff/werehouse[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\StaffController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'werehouse-front' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/werehouse[/:action].html',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\StaffController::class => Controller\Factory\StaffControllerFactory::class,
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
                    // register __NAMESPACE__.'_driver' for any entity under namespace `Hotel\Entity`
                    __NAMESPACE__.'\Entity' => __NAMESPACE__.'_driver',
                ]
            ],
        ],

    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],

    ],
    'service_manager'=>[
        'factories' => [
            Service\WerehouseManager::class  => Service\Factory\WerehouseManagerFactory::class,

        ]
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ]

];