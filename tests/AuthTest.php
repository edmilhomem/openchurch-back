<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 20/03/2016
 * Time: 17:25
 */

use GuzzleHttp\Client;

class AuthTest extends PHPUnit_Framework_TestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost/openchurch/openchurch-back/',
            'cookies' => true
        ]);
    }

    public function testAuth() {
        $response = $this->client->post('auth/logon', [
            'json' => [
                'username' => 'admin',
                'password' => 'admin'
            ]
        ]);
        $response = $this->client->get('auth/user');
        $user = json_decode($response->getBody(), true);
    }
}