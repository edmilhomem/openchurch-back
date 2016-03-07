<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 24/12/2015
 * Time: 00:35
 */

namespace OpenChurch\Tests\Controllers;

use Silex\WebTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SalasDeEbdControllerWebTestCase extends WebTestCase
{

    /**
     * Creates the application.
     *
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        // TODO: Implement createApplication() method.
        $app = require __DIR__.'/../../../app.php';
        $app['debug'] = true;
        return $app;
    }

    public function testFindAll() {
        $client = $this->createClient();
        $client->followRedirects();
        /*
        $client->request('post', '/auth/logon', array(
            'username' => 'admin',
            'password' => 'admin'
        ));
        */
        $client->request('GET', '/igrejas/1/ebd/salas');
        $response = $client->getResponse();

        print_r($response->getContent());
    }

    public function testFindById() {
        $client = $this->createClient();
        $client->followRedirects();
        $client->request('GET', '/estados/17');
        $response = $client->getResponse();

        print_r($response->getContent());
    }
}