<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 15/12/2015
 * Time: 01:10
 */

$loader = require_once('vendor/autoload.php');

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use OpenChurch\OpenChurchServiceProvider;

date_default_timezone_set('America/Araguaina');
setlocale(LC_ALL, 'pt_BR.utf-8');

//define("ROOT_PATH", __DIR__ . "/..");

$app = new Application;
$app['debug'] = true;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new OpenChurch\AngularPostRequestServiceProvider());

$app->register(new Ziadoz\Silex\Provider\CapsuleServiceProvider, [
    'capsule.connection' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'openchurch',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'logging'   => true,
    ],
]);
$app['capsule.eloquent'] = true;
$app['capsule.logging'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$userServiceProvider = new SimpleUser\UserServiceProvider();
$openchurch = new OpenChurch\OpenChurchServiceProvider();
$app->register($openchurch);
$app->mount('/', $openchurch);

return $app;
