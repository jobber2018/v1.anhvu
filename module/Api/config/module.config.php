<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/24/19 10:57 AM
 *
 */


namespace Api;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'api-front' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'api-v2' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/v2[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\V2Controller::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'zmp-checkout' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/zmp-checkout.html',
                    'defaults' => [
                        'controller' => Controller\V2Controller::class,
                        'action'     => 'zmpCheckout',
                    ]
                ],
            ],
            'zmp-revoke' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/zmp-revoke.html',
                    'defaults' => [
                        'controller' => Controller\V2Controller::class,
                        'action'     => 'zmpRevoke',
                    ]
                ],
            ],
            'zmp-notify' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/zmp-notify.html',
                    'defaults' => [
                        'controller' => Controller\V2Controller::class,
                        'action'     => 'zmpNotify',
                    ]
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\V2Controller::class => Controller\Factory\V2ControllerFactory::class,
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
            Service\ApiManager::class  => Service\Factory\ApiManagerFactory::class
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