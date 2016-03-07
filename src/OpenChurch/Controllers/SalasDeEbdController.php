<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 17:21
 */

namespace OpenChurch\Controllers;

use OpenChurch\Data\DataUtils;
use OpenChurch\Models\AulaDeEbd;
use OpenChurch\Models\Igreja;
use OpenChurch\Models\Pessoa;
use OpenChurch\Models\PresencaDeEbd;
use OpenChurch\Models\ProfessorDeEbd;
use OpenChurch\Models\SalaDeEbd;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Security\Acl\Exception\Exception;

class SalasDeEbdController
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
    public function all(Application $application, Request $request)
    {
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

        $query = SalaDeEbd::with('igreja', 'professores', 'alunos')
            ->join('igrejas', 'ebd_salas.igreja_id', '=', 'igrejas.id');

        if ($q) {
            $q = "%$q%";
            $query->where('igrejas.nome', 'like', $q)
                ->orWhere('ebd_salas.nome', 'like', $q)
                ->orWhere('ebd_salas.descricao', 'like', $q);
        }

        $total = $query->count();

        $query->orderBy($o, $t)
            ->select('ebd_salas.*');

        if ($skip !== null) {
            $query->skip($skip)->take($p);
        }

        $salas = $query->get();

        return $application->json(
            array(
                'total' => $total,
                'items' => $salas
            )
        );

    }

    public function find($id, $idSala, Application $application)
    {
        $sala = SalaDeEbd::with('igreja', 'professores', 'alunos', 'aulas', 'aulas.presentes')->find($idSala);
        if (!$sala) {
            return $application->abort(404);
        }
        return $application->json($sala);
    }

    public function save($id, $idSala = null, Application $application, Request $request)
    {
        $igreja = Igreja::find($id);
        if (!$igreja) {
            return $application->abort(404);
        }
        $sala = SalaDeEbd::with('igreja','professores','alunos')->find($idSala);
        if (!$sala)
            $sala = new SalaDeEbd;

        $sala->igreja()->associate($igreja);
        $sala->nome = $request->request->get('nome');
        $sala->descricao = $request->request->get('descricao');
        $sala->save();

        // professores
        $dados_professores = $request->request->get('professores', null);
        /*
         * dados_professores = array(
         *  '0' => array(
         *      'id' =>  // id da pessoa (opcional, vazio se nova pessoa)
         *      'nome' => // nome da pessoa (opcional, apenas se nova pessoa)
         *  )
         * )
         */
        $professores_ids = array();
        if ($dados_professores) {
            foreach ($dados_professores as $professor) {
                if (($id = DataUtils::array_key($professor, 'id'))) {
                    $professores_ids[] = $id;
                }
            }
        }

        // ids de professores atuais que não estão no array de professores enviado
        $professores_atuais = [];
        foreach ($sala->professores as $professor) {
            if (!in_array($professor['id'], $professores_ids)) {
                $sala->professores()->detach($professor['id']);
            }
            $professores_atuais[] = $professor['id'];
        }

        if ($dados_professores) {
            foreach ($dados_professores as $professor) {
                $id = DataUtils::array_key($professor, 'id');
                $p = null;
                if ($id && !in_array($id, $professores_atuais)) {
                    $p = Pessoa::find($id);
                }
                if (!$id) {
                    $p = new Pessoa;
                    $nome = DataUtils::array_key($professor, 'nome');
                    if (!$nome) {
                        throw new Exception('É necessário informar o nome do professor');
                    }
                    $p->nome = $nome;
                    $p->save();
                }
                //$p->push();
                if ($p) {
                    $sala->professores()->attach($p->id);
                    //$p->salas()->attach($sala->id);
                }
                //print_r($p);
            }
        }

        // alunos
        $dados_alunos = $request->request->get('alunos', null);
        /*
         * dados_alunos = array(
         *  '0' => array(
         *      'id' =>  // id da pessoa (opcional, vazio se nova pessoa)
         *      'nome' => // nome da pessoa (opcional, apenas se nova pessoa)
         *  )
         * )
         */
        $alunos_ids = array();
        if ($dados_alunos) {
            foreach ($dados_alunos as $aluno) {
                if (($id = DataUtils::array_key($aluno, 'id'))) {
                    $alunos_ids[] = $id;
                }
            }
        }

        // ids de professores atuais que não estão no array de professores enviado
        $alunos_atuais = [];
        foreach ($sala->alunos as $aluno) {
            if (!in_array($aluno['id'], $alunos_ids)) {
                $sala->alunos()->detach($aluno['id']);
            }
            $alunos_atuais[] = $aluno['id'];
        }

        if ($dados_alunos) {
            foreach ($dados_alunos as $aluno) {
                $id = DataUtils::array_key($aluno, 'id');
                $p = null;
                if ($id && !in_array($id, $alunos_atuais)) {
                    $p = Pessoa::find($id);
                }
                if (!$id) {
                    $p = new Pessoa;
                    $nome = DataUtils::array_key($aluno, 'nome');
                    if (!$nome) {
                        throw new Exception('É necessário informar o nome do aluno');
                    }
                    $p->nome = $nome;
                    $p->save();
                }
                //$p->push();
                if ($p) {
                    $sala->alunos()->attach($p->id);
                }
                //print_r($p);
            }
        }


        $dados_presentes = $request->request->get('presentes');
        if ($dados_presentes) {

            foreach($dados_presentes as $pessoa) {
                $presencas = DataUtils::array_key($pessoa, 'presencas');
                $pessoa_id = DataUtils::array_key($pessoa, 'id');
                $p = null;
                if (!$pessoa_id || $pessoa_id == -1) {
                    $nome = DataUtils::array_key($pessoa, 'nome');
                    if (!$nome) {
                        throw new Exception('É necessário informar o nome da pessoa na lista de presentes');
                    }
                    $p = new Pessoa;
                    $p->nome = $nome;
                    $p->save();
                } else {
                    $p = Pessoa::find($pessoa_id);
                }

                if (!$pessoa) {
                    throw new Exception('Os dados requeridos de uma pessoa da lista de presentes não foram informados');
                }

                foreach($presencas as $presenca) {
                    $aula_id = DataUtils::array_key($presenca, 'aula_id');
                    $presente = DataUtils::array_key($presenca, 'presente');
                    $q = PresencaDeEbd::where('aula_id', '=', $aula_id)->where('pessoa_id', '=', $p->id)->get();
                    if (count($q)>0) {
                        $q[0]->situacao = $presente;
                        $q = $q[0];
                    } else {
                        $q = new PresencaDeEbd;
                        $q->pessoa_id = $p->id;
                        $q->aula_id = $aula_id;
                        $q->situacao = $presente;
                    }
                    $q->save();
                }
            }
        }

        $sala->push();
        $sala = SalaDeEbd::with('igreja','professores', 'alunos', 'aulas', 'aulas.presentes')->find($sala->id);
        return $application->json($sala);
    }

    public function delete($id, $idSala, Application $application) {
        try {
            $sala = SalaDeEbd::find($idSala);
            if (!$sala) {
                throw new Exception;
            }
            $sala->delete();
            return $application->json(array());
        } catch (Exception $e) {
            return $application->abort(404, 'Sala não encontrada');
        }
    }

    public function estatisticas($id, Application $application)
    {
        $igreja = Igreja::findOrFail($id);
        $membros = Membro::with('pessoa')
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id', '=', $igreja->id)
            ->count();
        $homens = Membro::with('pessoa')
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id', '=', $igreja->id)
            ->where('pessoas.sexo', '=', 'M')
            ->count();
        $mulheres = Membro::with('pessoa')
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id', '=', $igreja->id)
            ->where('pessoas.sexo', '=', 'F')
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
            ->where('membros.igreja_id', '=', $igreja->id)
            ->groupBy('idade')
            ->get();

        $membros_por_faixa_etaria = Membro::
        select(Capsule::raw('count(*) as quantidade'), Capsule::raw('round(datediff(now(), pessoas.data_de_nascimento)/365) div 10 as faixa_etaria'))
            ->join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id', '=', $igreja->id)
            ->groupBy('faixa_etaria')
            ->get();

        $aniversariantes = Membro::
        join('pessoas', 'membros.pessoa_id', '=', 'pessoas.id')
            ->where('membros.igreja_id', '=', $igreja->id)
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