<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 16:21
 */

namespace OpenChurch\Controllers;


use OpenChurch\Models\Presbiterio;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PresbiteriosController
{
    public function all(Application $application, Request $request) {
        $q = $request->query->get('q'); // critério de busca
        $i = $request->query->get('i', null); // indice da página
        $p = $request->query->get('p'); // tamanho da página
        $o = $request->query->get('o', 'nome'); // campo da ordenação
        $t = $request->query->get('t', 'asc'); // tipo da ordenação
        $skip = null;

        if (!$i) {
            $i = 0;
        } else {
            $i--;
        }

        if ($p) {
            $skip = $i * $p;
        }

        $query = null;

        if ($q) {
            $q = "%$q%";
            $query = Presbiterio::where('nome', 'like', $q)
                ->orWhere('sigla', 'like', $q);
        } else {
            $query = Presbiterio::query();
        }

        $total = $query->count();

        $query->orderBy($o, $t);

        if ($skip !== null) {
            $query->skip($skip)->take($p);
        }

        $presbiterios = $query->get();

        return $application->json(
            array(
                'total' => $total,
                'items' => $presbiterios
            )
        );
    }
    public function find($id, Application $application) {
        $presbiterio = Presbiterio::find($id);
        return $application->json($presbiterio);
    }
}