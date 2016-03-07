<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 01:05
 */

namespace OpenChurch\Controllers;
use OpenChurch\Models\Estado;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Acl\Exception\Exception;

class EstadosController
{
    public function __construct()
    {
    }

    public function find($id) {
        $estado = Estado::find($id);
        if (!$estado) {
            throw new Exception("Estado ({$id}) não encontrado");
        }
        return new JsonResponse($estado);
    }

    public function all() {
        $estados = Estado::all();
        return new JsonResponse($estados);
    }
}