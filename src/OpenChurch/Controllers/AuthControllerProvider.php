<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 31/12/2015
 * Time: 00:46
 */

namespace OpenChurch\Controllers;


use OpenChurch\Managers\EstadosManager;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use OpenChurch\Data\EntitySerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use SimpleUser\User;


class AuthControllerProvider implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $manager = $app['user.manager'];

        $controllers->get('/', function () use ($app, $manager) {
            if ($app['user']) {
                return $app->json(array(
                    'username' => $app['user']->getName(),
                    'email' => $app['user']->getEmail()
                ));
            } else {
                return $app->abort(403, 'Não autenticado');
            }
        });

        $controllers->post('/', function (Request $request) use ($app, $manager) {

            $username = $request->request->get('username');
            $password = $request->request->get('password');

            $user = $manager->findOneBy(array('name' => $username));
            if ($user) {
                if ($manager->checkUserPassword($user, $password)) {
                    $manager->loginAsUser($user);
                    return $app->json(array(
                        'username' => $app['user']->getName(),
                        'email' => $app['user']->getEmail()
                    ));
                } else {
                    return $app->abort(403, 'Senha incorreta');
                }
            } else {
                return $app->abort(403, 'Usuário não encontrado');
            }

        });

        return $controllers;
    }
}