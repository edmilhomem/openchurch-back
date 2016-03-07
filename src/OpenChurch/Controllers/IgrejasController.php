<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 17:21
 */

namespace OpenChurch\Controllers;

use OpenChurch\Models\AulaDeEbd;
use OpenChurch\Models\Igreja;
use OpenChurch\Models\Membro;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Security\Acl\Exception\Exception;

class IgrejasController
{
    /**
     * Consulta e retorna as igrejas. Os parâmetros de URL indicam:
     * * q: o critério de busca
     * * i: o número da página
     * * p: a quantidade de itens por página
     * * o: o campo da ordenação
     * * t: o tipo da ordenação (asc, desc)
     *
     * Se o parâmetro de URL 'p' não for informado, todos os itens são retornados (elimina paginação)
     *
     * @param Application $application
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Um objeto com dois atributos:
     * * total: a quantidade total de itens no banco de dados
     * * items: os itens da página atual
     */
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

        $query = Igreja::with('presbiterio')
            ->join('presbiterios', 'igrejas.presbiterio_id', '=', 'presbiterios.id');

        if ($q) {
            $q = "%$q%";
            $query->where('igrejas.nome', 'like', $q)
                ->orWhere('presbiterios.nome', 'like', $q)
                ->orWhere('presbiterios.sigla', 'like', $q);
        }

        $total = $query->count();

        $query->orderBy($o, $t)
            ->select('igrejas.*');

        if ($skip !== null) {
            $query->skip($skip)->take($p);
        }

        $igrejas = $query->get();

        return $application->json(
            array(
                'total' => $total,
                'items' => $igrejas
            )
        );

    }

    public function find($id, Application $application) {
        $igreja = Igreja::with('presbiterio')->find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        return $application->json($igreja);
    }

    public function save($id, Application $application, Request $request) {
        $igreja = Igreja::with('presbiterio')->find($id);
        if (!$igreja) {
            return $application->abort(404);
        }

        $igreja->nome = $request->request->get('nome');
        $igreja->presbiterio_id = $request->request->get('presbiterio_id');
        $igreja->endereco = $request->request->get('endereco', null);
        $igreja->endereco_numero = $request->request->get('endereco_numero', null);
        $igreja->endereco_bairro = $request->request->get('endereco_bairro', null);
        $igreja->endereco_cidade = $request->request->get('endereco_cidade', null);
        $igreja->endereco_uf = $request->request->get('endereco_uf', null);
        $igreja->endereco_cep = $request->request->get('endereco_cep', null);
        $igreja->telefone = $request->request->get('telefone', null);
        $igreja->email = $request->request->get('email', null);
        $igreja->save();

        return $application->json($igreja);
    }

    public function all_aulas($id, $ano = null, Application $application, Request $request) {
        $igreja = Igreja::find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        $q = $request->query->get('q'); // critério de busca
        $i = $request->query->get('i', null); // indice da página
        $p = $request->query->get('p'); // tamanho da página
        $o = $request->query->get('o', 'data'); // campo da ordenação
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

        if ($ano) {
            $query = AulaDeEbd::whereRaw('year(data) = ' . $ano);
            $skip = null;
        } else {
            $query = AulaDeEbd::query();
        }

        if ($q) {
            $q = "%$q%";
            $query = AulaDeEbd::where('data', 'like', $q)
                ->orWhere('observacoes', 'like', $q);
        }

        $total = $query->count();

        $query->orderBy($o, $t);

        if ($skip !== null) {
            $query->skip($skip)->take($p);
        }

        $aulas = $query->get();

        return $application->json(
            array(
                'total' => $total,
                'items' => $aulas
            )
        );
    }

    public function save_aulas($id, $idAula = null, Application $application, Request $request) {
        $igreja = Igreja::find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        $aula = AulaDeEbd::find($idAula);
        if (!$aula)
            $aula = new AulaDeEbd;
        $aula->igreja()->associate($igreja);
        $aula->data = $request->request->get('data');
        $aula->observacoes = $request->request->get('observacoes');
        $aula->push();
        return $application->json($aula);
    }

    public function find_aula($id, $idAula, Application $application, Request $request) {
        $igreja = Igreja::find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        $aula = AulaDeEbd::find($idAula);
        if (!$aula)
            return $application->abort(404);
        return $application->json($aula);
    }

    public function config_aulas($id, $ano = 2016, Application $application, Request $request) {
        $igreja = Igreja::find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        $primeiro_domingo = new \DateTime(date('Y-m-d', strtotime('first sunday of January ' . $ano)));
        $ultimo_domingo = new \DateTime(date('Y-m-d', strtotime('last sunday of December ' . $ano)));
        $data = $primeiro_domingo;
        $datas = array(clone($data));
        while($data->diff($ultimo_domingo)->days > 0) {
            $data->add(date_interval_create_from_date_string('7 days'));
            $datas[] = clone($data);
        }
        try {
            Capsule::beginTransaction();
            foreach ($datas as $data) {
                $aula = new AulaDeEbd;
                $aula->igreja()->associate($igreja);
                $aula->data = $data->format('Y-m-d');
                $aula->save();
            }
            Capsule::commit();
        } catch (Exception $e) {
            Capsule::rollback();
            throw $e;
        }
        return $application->escape('ok');
    }

    public function estatisticas($id, Application $application) {
        $igreja = Igreja::find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        $membros = Membro::with('pessoa')
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id','=',$igreja->id)
            ->count();
        $homens = Membro::with('pessoa')
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id','=',$igreja->id)
            ->where('pessoas.sexo','=','M')
            ->count();
        $mulheres = Membro::with('pessoa')
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id','=',$igreja->id)
            ->where('pessoas.sexo','=','F')
            ->count();

        /*
         * select count(*) as quantidade, round(datediff(now(), pessoas.data_de_nascimento)/365) as idade
from membros inner join pessoas on pessoas.id = membros.pessoa_id
where igreja_id=1
group by idade;

         */
        $membros_por_idade = Membro::
            select(Capsule::raw('count(*) as quantidade'), Capsule::raw('round(datediff(now(), pessoas.data_de_nascimento)/365) as idade'))
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id','=',$igreja->id)
            ->groupBy('idade')
            ->get();

        $membros_por_faixa_etaria = Membro::
        select(Capsule::raw('count(*) as quantidade'), Capsule::raw('round(datediff(now(), pessoas.data_de_nascimento)/365) div 10 as faixa_etaria'))
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id','=',$igreja->id)
            ->groupBy('faixa_etaria')
            ->get();

        $aniversariantes = Membro::
            join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id','=',$igreja->id)
            ->whereRaw('month(pessoas.data_de_nascimento)=month(now())')
            ->select('membros.id', 'pessoas.nome', Capsule::raw('round(datediff(now(), pessoas.data_de_nascimento)/365) as idade'))
            ->get();

        return $application->json(array(
            'total_de_membros' => $membros,
            'homens' => $homens,
            'mulheres' => $mulheres,
            'membros_por_idade' => $membros_por_idade,
            'membros_por_faixa_etaria' => $membros_por_faixa_etaria,
            'aniversariantes' => $aniversariantes
        ));
    }
}