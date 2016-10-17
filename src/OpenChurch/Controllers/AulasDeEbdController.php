<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 20/03/2016
 * Time: 11:12
 */

namespace OpenChurch\Controllers;


/**
 * Class AulasDeEbdController
 * @package OpenChurch\Controllers
 */
class AulasDeEbdController
{
    /**
     * Retorna as aulas de EBD da igreja.
     * 
     * @param $id
     * @param null $ano
     * @param Application $application
     * @param Request $request
     * @return mixed
     */
    public function all($idIgreja, $ano = null, Application $application, Request $request) {
        $igreja = Igreja::find($idIgreja);
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

    /**
     * Salva (cadastra ou atualiza) uma aula de EBD para uma igreja.
     * 
     * Os seguintes dados são esperados via POST:
     * * data
     * * observacoes
     * 
     * @param $idIgreja
     * @param null $idAula
     * @param Application $application
     * @param Request $request
     * @return mixed
     */
    public function save($idIgreja, $idAula = null, Application $application, Request $request) {
        $igreja = Igreja::find($idIgreja);
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

    /**
     * Encontra uma aula de ebd.
     * 
     * @param $idIgreja
     * @param $idAula
     * @param Application $application
     * @param Request $request
     * @return mixed
     */
    public function find($idIgreja, $idAula, Application $application, Request $request) {
        $igreja = Igreja::find($idIgreja);
        if (!$igreja) {
            return $application->abort(404);
        }
        $aula = AulaDeEbd::find($idAula);
        if (!$aula)
            return $application->abort(404);
        return $application->json($aula);
    }

    /**
     * Cria as aulas de EBD para a igreja em um determinado ano (parâmetro $ano).
     * Com base no primeiro e no último dia do mês do ano, este método cria registros para as aulas de EBD da igreja.
     * 
     * @param $idIgreja
     * @param int $ano
     * @param Application $application
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function config($idIgreja, $ano = 2016, Application $application, Request $request) {
        $igreja = Igreja::find($idIgreja);
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
}