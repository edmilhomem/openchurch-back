<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:14
 */

namespace OpenChurch\Controllers;


use OpenChurch\Data\DataUtils;
use OpenChurch\Models\Pessoa;
use OpenChurch\Models\Membro;
use OpenChurch\Models\Igreja;
use OpenChurch\Utils;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MembrosController
{
    public function all($idIgreja, Application $application, Request $request)
    {
        $igrejas_manager = new Igreja($application['db']);

        if (!$application['igrejas.controller']->exists($idIgreja, $application)) {
            throw new NotFoundHttpException("A igreja (id = $idIgreja) não foi encontrada.");
        }

        $params = [
            'i' => $request->query->get('i', null),
            'p' => $request->query->get('p', null),
            'o' => $request->query->get('o', 'pessoas.nome'),
            't' => $request->query->get('t', 'asc')
        ];

        $q = $request->query->get('q', null); // critério de busca

        $select = "pessoas.*, membros.*";
        $from = "membros
        INNER JOIN pessoas ON membros.pessoa_id = pessoas.id";
        $where = "membros.igreja_id = ?";

        $where_params = [$idIgreja];

        if ($q) {
            $q = "%$q%";
            $where .= " AND pessoas.nome like ?";
            $where_params[] = $q;
        }

        $params['q'] = $where_params;

        $manager = new Membro($application['db']);

        return $application->json($manager->page(
            $select,
            $from,
            $where,
            $params
        ));
    }

    public function find($id, $idIgreja, Application $application)
    {
        if (!$application['igrejas.controller']->exists($idIgreja, $application)) {
            throw new NotFoundHttpException("A igreja (id = $idIgreja) não foi encontrada.");
        }
        $membros_manager = new Membro($application['db']);
        $membro = $membros_manager->find(['id' => $id]);
        if (!$membro) {
            throw new NotFoundHttpException("O membro (id = $id) não foi encontrado");
        } else {
            return $application->json(['data' => $membro]);
        }
    }

    public function save($idIgreja, $id = null, Application $application, Request $request)
    {
        try {
            $application['db']->beginTransaction();
            $post = json_decode($request->getContent());
            $existe = false;

            $pessoas_manager = new Pessoa($application['db']);

            // primeiro, salva pessoa
            // é uma pessoa já existente
            $pessoa = $pessoas_manager->findOrNew(['id' => Utils::safeProperty($post->pessoa, 'id')]);

            $pessoa->cpf = Utils::safeProperty($post->pessoa, 'cpf');
            $pessoa->data_de_nascimento = Utils::safeProperty($post->pessoa, 'data_de_nascimento');
            $pessoa->email = Utils::safeProperty($post->pessoa, 'email');
            $pessoa->endereco = Utils::safeProperty($post->pessoa, 'endereco');
            $pessoa->endereco_numero = Utils::safeProperty($post->pessoa, 'endereco_numero');
            $pessoa->endereco_bairro = Utils::safeProperty($post->pessoa, 'endereco_bairro');
            $pessoa->endereco_cidade = Utils::safeProperty($post->pessoa, 'endereco_cidade');
            $pessoa->endereco_uf = Utils::safeProperty($post->pessoa, 'endereco_uf');
            $pessoa->endereco_cep = Utils::safeProperty($post->pessoa, 'endereco_cep');
            $pessoa->estado_civil = Utils::safeProperty($post->pessoa, 'estado_civil');
            $pessoa->instrucao = Utils::safeProperty($post->pessoa, 'instrucao');
            $pessoa->nacionalidade = Utils::safeProperty($post->pessoa, 'nacionalidade');
            $pessoa->naturalidade_cidade = Utils::safeProperty($post->pessoa, 'naturalidade_cidade');
            $pessoa->naturalidade_uf = Utils::safeProperty($post->pessoa, 'naturalidade_uf');
            $pessoa->nome = Utils::safeProperty($post->pessoa, 'nome');
            if (!$pessoa->nome) {
                throw new Exception('O nome deve ser obrigatoriamente informado');
            }
            $pessoa->observacoes = Utils::safeProperty($post->pessoa, 'observacoes');
            $pessoa->profissao = Utils::safeProperty($post->pessoa, 'profissao');
            $pessoa->religiao = Utils::safeProperty($post->pessoa, 'religiao');
            $pessoa->sexo = Utils::safeProperty($post->pessoa, 'sexo');
            $pessoa->telefone = Utils::safeProperty($post->pessoa, 'telefone');

            $pessoa = $pessoas_manager->save($pessoa, ["id" => Utils::safeProperty($post->pessoa, 'id')]);

            // atualizar dados relacionados: pai, mae, conjuge
            if (($pai = Utils::safeProperty($post->pessoa, 'pai')) != null) {
                $pessoa->pai = $pessoas_manager->save($pai, ['id' => $pai->id]);
                $pessoa->pai_id = $pessoa->pai->id;
            } else {
                $pessoa->pai_id = null;
            }

            if (($mae = Utils::safeProperty($pessoa, 'mae')) != null) {
                $pessoa->mae = $pessoas_manager->save($mae, ['id' => $mae->id]);
                $pessoa->mae_id = $pessoa->mae->id;
            } else {
                $pessoa->mae_id = null;
            }

            if (($conjuge = Utils::safeProperty($pessoa, 'conjuge')) != null) {
                $conjuge->conjuge_id = $pessoa->id;
                $pessoa->conjuge = $pessoas_manager->save($conjuge, ['id' => $conjuge->id]);
                $pessoa->conjuge_id = $pessoa->conjuge->id;
            } else {
                $pessoa->conjuge_id = null;
            }

            // atualizar pessoa
            $pessoas_manager->save($pessoa, ['id' => $pessoa->id]);

            $membros_manager = new Membro($application['db']);
            $membro = $membros_manager->findOrNew(['id' => $id]);
            $membro->igreja_id = $idIgreja;
            $membro->pessoa_id = $pessoa->id;
            $membro = $membros_manager->save($membro, ['id' => $id]);
            $membro->pessoa = $pessoa;

            $application['db']->commit();

            return $application->json($membro);
        } catch (Exception $e) {
            $application['db']->rollBack();
            return $e;
        }
    }
}