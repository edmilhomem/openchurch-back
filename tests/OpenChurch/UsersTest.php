<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 16/12/2015
 * Time: 12:26
 */

namespace OpenChurch\Tests;

use OpenChurch\Managers\UsersManager;
use PHPUnit_Framework_TestSuite;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use OpenChurch\Data\EntitySerializer;

class UsersManagerTest extends \PHPUnit_Framework_TestCase
{
    static $app;
    static $em;
    static $users_repository;

    public static function setUpBeforeClass()
    {
        self::$app = require_once __DIR__ . "/../../app.php";
        self::$em = self::$app['orm.em'];
        self::$users_repository = self::$em->getRepository("OpenChurch\\ORM\\Mapping\\User");
    }

    public function testFindLastCreated() {
        $user = self::$users_repository->findLastCreated();
        $this->assertTrue($user->getName() == 'admin');
    }

    public function testFindAllCountGreatherThanZero() {
        //$users = $this->manager->findAll();
        $users = self::$users_repository->findAll();
        $users = EntitySerializer::serialize(self::$em, $users);
        $this->assertTrue(count($users) > 0);
    }

    public function testFindNoAdmin() {
        $user = self::$users_repository->findByName('admin');
        $this->assertNull($user);
    }



}

/*

use Silex\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UsersTest extends WebTestCase
{

    /**
     * Creates the application.
     *
     * @return HttpKernelInterface


    public function createApplication()
    {
        $app = require __DIR__. '../../../index.php';
        $app['debug'] = true;
        return $app;
    }

    public function testFind() {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');



        $this->assertTrue($client->getResponse()->isOk());
    }
}

*/