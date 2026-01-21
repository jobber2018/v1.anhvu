<?php

use Zend\Mvc\Application;
use Zend\Stdlib\ArrayUtils;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
//echo __DIR__."<br>";
//return;
///Users/truonghm/Documents/GER/Website/ger/public
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (! class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
        . "- Type `docker-compose run zf composer install` if you are using Docker.\n"
    );
}

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

//echo __DIR__;
//User/truonghm/Documents/GER/Website/ger/public

define('APPLICATION_PATH',dirname(__DIR__));
//echo dirname(__DIR__)."<br>";
//echo APPLICATION_PATH;
//return;
///User/truonghm/Documents/GER/Website/ger

define('ROOT_PATH',APPLICATION_PATH.'/public_html');
define('FILES_PATH',ROOT_PATH.'/files/');
define('IMAGE_PATH',ROOT_PATH.'/img/');


// Run the application!
Application::init($appConfig)->run();
