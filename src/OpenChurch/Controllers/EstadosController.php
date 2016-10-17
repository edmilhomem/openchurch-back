<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 01:05
 */

namespace OpenChurch\Controllers;

use OpenChurch\Models\Estado;
use OpenChurch\Serializers\EstadoSerializer;
use OpenChurch\Utils;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EstadosController
{
    public function __construct()
    {
    }

    public function find($id, Application $app)
    {
        $document = Utils::controller_find_helper($app['db'], 'estado',
            ['id' => $id], ['id', 'nome', 'sigla', 'created_at', 'updated_at']);
        return $app->json($document);
    }

    public function all(Application $application)
    {
        $document = Utils::controller_all_helper($application['db'], 'estado', '/estados',
            [], ['id', 'nome', 'sigla', 'created_at', 'updated_at']);
        return $application->json($document);
    }
}