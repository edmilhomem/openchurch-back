<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 16:21
 */

namespace OpenChurch\Controllers;

use OpenChurch\Models\Presbiterio;
use OpenChurch\Utils;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PresbiteriosController
{
    public function all(Application $application, Request $request) {
        $document = Utils::controller_all_helper($application['db'], 'presbiterio', '/presbiterios',
            [], ['id', 'nome', 'sigla', 'created_at', 'updated_at']);
        return $application->json($document);
    }
    public function find($id, Application $application) {
        $document = Utils::controller_find_helper($application['db'], 'presbiterio',
            ['id' => $id], ['id', 'nome', 'sigla', 'created_at', 'updated_at']);
        return $application->json($document);
    }
}