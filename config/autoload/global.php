<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Zend\Session\Config\SessionConfig;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;

return [
    'doctrine' => [
        'connection' => [
            // default connection name
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDOMySql\Driver::class,
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => '',
                    'dbname'   => 'v1.anhvu',
                    'charset'       =>  'UTF8'
                ],
            ],
        ],
    ],
    /*'doctrine' => [
        'connection' => [
            // default connection name
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDOMySql\Driver::class,
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'Kyanh@010',
                    'dbname'   => 'giahoang',
                    'charset'       =>  'UTF8',
                ],
            ],
        ],
    ],*/
    'session_config' => [
        'cookie_lifetime' => 604800, // one week
        'gc_maxlifetime'  => 18000,//5h
        'name' => 'Sulde',
    ],
    /*'session_manager'=>[
        'validators'=>[
            RemoteAddr::class,
            HttpUserAgent::class
        ]
    ],*/
    'session_validators' => [
        RemoteAddr::class,
        HttpUserAgent::class,
    ],
    'session_storage'=>[
        'type' => SessionArrayStorage::class
    ],
    'view_manager' => [
        'display_exceptions' => true,
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => getcwd() .  '/data/language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'mail_options' => [
        'name' => 'email-smtp.us-east-1.amazonaws.com',
        'host' => 'email-smtp.us-east-1.amazonaws.com',
        'port' => 587,
        'connection_class'  => 'login',
        'connection_config' =>[
            //'username' => 'AKIAXSLECV4ENFQVSYC3',
            //'password' => 'BO1pr5etU4IDU7bsl5Rx3SP4w8MkniW9Zksvq8W/IATw',
            'username' => 'AKIAXXT5ZGIFECTDJXOP',
            'password' => 'BK4Y4FAo1VZCrDiljZmxTYGII+whogQq0KukXibjFijG',
            'port'     => 587,
            'ssl'      => 'tls'
        ]
    ]

];
