<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 02:41
 */

namespace OpenChurch\Controllers;
use OpenChurch\Models\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class AuthController
 * @package OpenChurch\Controllers
 * @see https://github.com/fredjuvaux/silex-simpleuser
 */
class AuthController
{
    public function user(Application $app) {
        return $app->json($app['session']->get('user'));
    }

    public function logon(Application $app, Request $req) {
        $username = $req->request->get('username');
        $password = $req->request->get('password');

        $user = User::where('username', $username)->first();

        if (!$user) {
            return $app->abort(403, 'Falha na autenticação: usuário não encontrado');
        }

        $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder();
        $hash = $encoder->encodePassword($password, $user->salt);
        if ($hash != $user->password) {
            $app->abort(403, 'Falha na autenticação: senha incorreta');
        }

        $app['session']->set('isAuthenticated', true);
        $app['session']->set('user', $user);

        return $app->json($app['session']->get('user'));
    }

    public function logout(Application $application) {
        $application['session']->clear();
        return $application->escape("1");
    }
}