<?php

namespace Users;

use Zend\Authentication\AuthenticationService;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'user-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user-admin[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index'
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'roles-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/roles-admin[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\RolesController::class,
                        'action'     => 'index'
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'login' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/admin-login.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'user-login' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/login.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'userLogin',
                    ],
                ],
            ],
            'user-signup' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/user-signup.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'userSignup',
                    ],
                ],
            ],
            'user-reset-password' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/user-reset-password.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'userResetPassword',
                    ],
                ],
            ],
            'user-set-password' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/user-set-password[/:token].html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'userSetPassword',
                    ],
                    'constraints'=>[
                        'token' => '[a-zA-Z0-9_-]*'
                    ]
                ],
            ],
            'user-logout' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/user-logout.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'userLogout',
                    ],
                ],
            ],
            'user-front' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'index'
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'social-register' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/social-register.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'socialRegister',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*'
                    ]
                ],
            ],
            'login-with-facebook' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/login-with-facebook.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'loginWithFacebook',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*'
                    ]
                ],
            ],
            'login-with-google' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/login-with-google.html',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'loginWithGoogle',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*'
                    ]
                ],
            ]
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
            Controller\RolesController::class => Controller\Factory\RolesControllerFactory::class,
        ],
    ],


    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'userLayoutLogin'   => __DIR__ . '/../view/layout/user-layout-login.phtml',
        ]
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
            Service\UserManager::class  => Service\Factory\UserManagerFactory::class,
            Service\RolesManager::class  => Service\Factory\RolesManagerFactory::class,
            Service\AuthManager::class =>  Service\Factory\AuthManagerFactory::class,
            Service\AuthAdapter::class =>  Service\Factory\AuthAdapterFactory::class,
            AuthenticationService::class=> Service\Factory\AuthenticationServiceFactory::class
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