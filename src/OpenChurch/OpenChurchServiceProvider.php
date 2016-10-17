<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 01:08
 */

namespace OpenChurch;


use OpenChurch\Controllers\AuthController;
use OpenChurch\Controllers\EstadosController;
use OpenChurch\Controllers\PastoresController;
use OpenChurch\Controllers\PessoasController;
use OpenChurch\Controllers\PresbiteriosController;
use OpenChurch\Controllers\IgrejasController;
use OpenChurch\Controllers\MembrosController;
use OpenChurch\Controllers\SalasDeEbdController;
use OpenChurch\Security\UserProvider;
use Pimple\Container;
use Silex\Application;
use Silex\ControllerCollection;
use Pimple\ServiceProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OpenChurchServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Container $app)
    {
        $app['estados.controller'] = function() { return new EstadosController(); };
        $app['auth.controller'] = function(){ return new AuthController(); };
        $app['presbiterios.controller'] = function(){ return new PresbiteriosController(); };
        $app['igrejas.controller'] = function(){ return new IgrejasController(); };
        $app['pessoas.controller'] = function(){ return new PessoasController(); };
        $app['membros.controller'] = function(){ return new MembrosController(); };
        $app['pastores.controller'] = function(){ return new PastoresController(); };
        $app['ebdsalas.controller'] = function(){ return new SalasDeEbdController(); };
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }

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

        // auth controller
        $controllers->method('GET|POST')->match('/auth/user', 'auth.controller:user');
        $controllers->post('auth/logon', 'auth.controller:logon')
            ->bind('auth.logon');
        $controllers->method('GET|POST')->match('/auth/logout', 'auth.controller:logout');

        // estados controller
        $controllers->get('/estados', 'estados.controller:all');
        $controllers->get('/estados/{id}', 'estados.controller:find');

        // presbiterios
        $controllers->get('/presbiterios', 'presbiterios.controller:all');
        $controllers->get('/presbiterios/{id}', 'presbiterios.controller:find');

        // igrejas
        $controllers->get('/igrejas', 'igrejas.controller:all');
        $controllers->get('/igrejas/{id}', 'igrejas.controller:find');
        $controllers->post('/igrejas', 'igrejas.controller:save');
        $controllers->post('/igrejas/{id}', 'igrejas.controller:save');
        $controllers->delete('/igrejas/{id}', 'igrejas.controller:delete');
        $controllers->get('/igrejas/{id}/estatisticas', 'igrejas.controller:estatisticas');
        $controllers->get('/igrejas/{id}/ebd/aulas', 'igrejas.controller:all_aulas');
        $controllers->get('/igrejas/{id}/ebd/aulas/{idAula}', 'igrejas.controller:find_aula');
        $controllers->get('/igrejas/{id}/ebd/aulas/{ano}/lista', 'igrejas.controller:all_aulas');
        $controllers->post('/igrejas/{id}/ebd/aulas/config', 'igrejas.controller:config_aulas');
        $controllers->post('/igrejas/{id}/ebd/aulas/{ano}/config', 'igrejas.controller:config_aulas');
        $controllers->post('/igrejas/{id}/ebd/aulas', 'igrejas.controller:save_aulas');
        $controllers->post('/igrejas/{id}/ebd/aulas/', 'igrejas.controller:save_aulas');
        $controllers->post('/igrejas/{id}/ebd/aulas/{idAula}', 'igrejas.controller:save_aulas');
        $controllers->get('/igrejas/{idIgreja}/permissao/{idUsuario}', 'auth.controller:permissao');

        // ebd - salas
        $controllers->get('/igrejas/{id}/ebd/salas', 'ebdsalas.controller:all');
        $controllers->get('/igrejas/{id}/ebd/salas/{idSala}', 'ebdsalas.controller:find');
        $controllers->post('/igrejas/{id}/ebd/salas/{idSala}', 'ebdsalas.controller:save');
        $controllers->post('/igrejas/{id}/ebd/salas', 'ebdsalas.controller:save');
        $controllers->delete('/igrejas/{id}/ebd/salas/{idSala}', 'ebdsalas.controller:delete');

        // pessoas
        $controllers->get('/pessoas', 'pessoas.controller:all');
        $controllers->get('/pessoas/{id}', 'pessoas.controller:find');
        $controllers->post('/pessoas/{id}', 'pessoas.controller:save');

        // membros
        $controllers->get('/igrejas/{idIgreja}/membros', 'membros.controller:all');
        $controllers->get('/igrejas/{idIgreja}/membros/{id}', 'membros.controller:find');
        $controllers->post('/igrejas/{idIgreja}/membros', 'membros.controller:save');
        $controllers->post('/igrejas/{idIgreja}/membros/', 'membros.controller:save');
        $controllers->post('/igrejas/{idIgreja}/membros/{id}', 'membros.controller:save');

        // pastores
        $controllers->get('/pastores', 'pastores.controller:all');
        $controllers->get('/pastores/', 'pastores.controller:all');
        $controllers->get('/pastores/{id}', 'pastores.controller:find');
        $controllers->post('/pastores', 'pastores.controller:save');
        $controllers->post('/pastores/', 'pastores.controller:save');
        $controllers->post('/pastores/{id}', 'pastores.controller:save');

        // segurança
        /*
        $controllers->before(function(Request $request) use ($app) {
            if ($request->getRequestUri() == $app['url_generator']->generate('auth.logon'))
            {
                return;
            }
            if (!$app['session']->get('user')) {
                throw new AccessDeniedException();
            }

        });
        */
        return $controllers;
    }

}