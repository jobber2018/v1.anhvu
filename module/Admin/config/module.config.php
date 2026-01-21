<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'admin-dashboard' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/dashboard.html',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'staff-dashboard' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/admin/staff-dashboard.html',
                    'defaults' => [
                        'controller' => Controller\StaffController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'user-dashboard' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/users/dashboard[/:action[/:id]].html',
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
            'user-activity' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/user/activity',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'activity',
                    ],
                ],
            ],
            'user-message' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/user/message',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'message',
                    ],
                ],
            ],
            'user-activity-read' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/user/activity-read',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'activity-read',
                    ],
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\StaffController::class => Controller\Factory\StaffControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
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
            Service\AdminManager::class  => Service\Factory\AdminManagerFactory::class,
        ]
    ]

];