<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 14/12/2015
 * Time: 18:41
 */

$app = require_once('app.php');

$app->get('/', function() use ($app) {
    return $app->json(array(
        'name' => 'OpenChurch',
        'description' => 'Open Source church management software. Angular + PHP + Silex + MySQL',
        'version' => '1.0.0'
    ));
});

$app->register(new JDesrosiers\Silex\Provider\CorsServiceProvider(), array(
    "cors.allowOrigin" => "*",
    "cors.allowCredentials" => true,
    "cors.allowHeaders" => "*"
));

$app->after($app["cors"]);
$app->run();

?>