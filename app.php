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
$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname' => 'openchurch',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
    'charset' => 'utf8'
);
$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
$app['db'] = $connection;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Cocur\Slugify\Bridge\Silex2\SlugifyServiceProvider());

//$userServiceProvider = new SimpleUser\UserServiceProvider();
//$app->register($userServiceProvider);
//$app->mount('/user-management', $userServiceProvider);
$openchurch = new OpenChurch\OpenChurchServiceProvider();
$app->register($openchurch);
$app->mount('/', $openchurch);

return $app;
