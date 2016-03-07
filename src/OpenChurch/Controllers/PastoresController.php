<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 07/01/2016
 * Time: 14:46
 */

namespace OpenChurch\Controllers;

use OpenChurch\Data\DataUtils;
use OpenChurch\Models\Igreja;
use OpenChurch\Models\Pastor;
use OpenChurch\Models\Pessoa;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class PastoresController
{
    public function all(Application $application, Request $request)
    {
        $q = $request->query->get('q'); // critério de busca
        $i = $request->query->get('i', null); // indice da página
        $p = $request->query->get('p'); // tamanho da página
        $o = $request->query->get('o', 'pessoas.nome'); // campo da ordenação
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

        $query = Pastor::with(array('pessoa', 'igreja'))
            ->join('pessoas', 'pessoas.id', '=', 'pastores.pessoa_id')
            ->join('igrejas', 'igrejas.id', '=', 'pastores.igreja_id');

        if ($q) {
            $q = "%$q%";
            $query->where('pessoas.nome', 'like', $q);
        }

        $total = $query->count();

        $query->orderBy($o, $t)
            ->select('pastores.*');

        if ($skip !== null) {
            $query->skip($skip)->take($p);
        }

        $pastores = $query->get();

        return $application->json(
            array(
                'total' => $total,
                'items' => $pastores
            )
        );
    }

    public function find($id, Application $application) {
        $pastor = Pastor::with('pessoa', 'igreja', 'pessoa.pai', 'pessoa.mae', 'pessoa.conjuge')->findOrFail($id);
        return $application->json($pastor);
    }

    public function save($id = null, Application $application, Request $request) {
        // encontrar pastor
        try {
            Capsule::beginTransaction();

            $pastor = Pastor::findOrNew($id);

            // primeiro, salva pessoa
            // é uma pessoa já existente
            $pessoa_dados = $request->request->get('pessoa');

            $pessoa_id = DataUtils::array_key($pessoa_dados, 'id');
            $pessoa = Pessoa::findOrNew($pessoa_id);

            // tenta encontrar pai, mae, conjuge
            if (($dados_pai = DataUtils::array_key($pessoa_dados, 'pai')) != null) {
                $pai = Pessoa::findOrNew(DataUtils::array_key($dados_pai, 'id'));
                $pai->nome = DataUtils::array_key($dados_pai, 'nome');
                $pai->religiao = DataUtils::array_key($dados_pai, 'religiao');
                $pai->save();
                $pessoa->pai()->associate($pai);
            } else {
                $pessoa->pai_id = null;
            }

            if (($dados_mae = DataUtils::array_key($pessoa_dados, 'mae')) != null) {
                $mae = Pessoa::findOrNew(DataUtils::array_key($dados_mae, 'id'));
                $mae->nome = DataUtils::array_key($dados_mae, 'nome');
                $mae->religiao = DataUtils::array_key($dados_mae, 'religiao');
                $mae->save();
                $pessoa->mae()->associate($mae);
            } else {
                $pessoa->mae_id = null;
            }

            if (($dados_conjuge = DataUtils::array_key($pessoa_dados, 'conjuge')) != null) {
                $conjuge = Pessoa::findOrNew(DataUtils::array_key($dados_conjuge, 'id'));
                $conjuge->nome = DataUtils::array_key($dados_conjuge, 'nome');
                $conjuge->religiao = DataUtils::array_key($dados_conjuge, 'religiao');
                $conjuge->conjuge_id = $pessoa->id;
                $conjuge->save();
                $pessoa->conjuge()->associate($conjuge);
            } else {
                $pessoa->conjuge_id = null;
            }

            $pessoa->cpf = DataUtils::array_key($pessoa_dados, 'cpf');
            $pessoa->data_de_nascimento = DataUtils::array_key($pessoa_dados, 'data_de_nascimento');
            $pessoa->email = DataUtils::array_key($pessoa_dados, 'email');
            $pessoa->endereco = DataUtils::array_key($pessoa_dados, 'endereco');
            $pessoa->endereco_numero = DataUtils::array_key($pessoa_dados, 'endereco_numero');
            $pessoa->endereco_bairro = DataUtils::array_key($pessoa_dados, 'endereco_bairro');
            $pessoa->endereco_cidade = DataUtils::array_key($pessoa_dados, 'endereco_cidade');
            $pessoa->endereco_uf = DataUtils::array_key($pessoa_dados, 'endereco_uf');
            $pessoa->endereco_cep = DataUtils::array_key($pessoa_dados, 'endereco_cep');
            $pessoa->estado_civil = DataUtils::array_key($pessoa_dados, 'estado_civil');
            $pessoa->instrucao = DataUtils::array_key($pessoa_dados, 'instrucao');
            $pessoa->nacionalidade = DataUtils::array_key($pessoa_dados, 'nacionalidade');
            $pessoa->naturalidade_cidade = DataUtils::array_key($pessoa_dados, 'naturalidade_cidade');
            $pessoa->naturalidade_uf = DataUtils::array_key($pessoa_dados, 'naturalidade_uf');
            $pessoa->nome = DataUtils::array_key($pessoa_dados, 'nome');
            if (!$pessoa->nome) {
                throw new Exception('O nome deve ser obrigatoriamente informado');
            }
            $pessoa->observacoes = DataUtils::array_key($pessoa_dados, 'observacoes', null);
            $pessoa->profissao = DataUtils::array_key($pessoa_dados, 'profissao', null);
            $pessoa->religiao = DataUtils::array_key($pessoa_dados, 'religiao', null);
            $pessoa->sexo = DataUtils::array_key($pessoa_dados, 'sexo', null);
            $pessoa->telefone = DataUtils::array_key($pessoa_dados, 'telefone', null);

            $pessoa->push();

            $pastor->pessoa()->associate($pessoa);

            if ($request->request->get('igreja_id', null)) {
                $igreja = Igreja::findOrFail($request->request->get('igreja_id', null));
                $pastor->igreja()->associate($igreja);
            }

            $pastor->pastoreio_data_inicial = $request->request->get('pastoreio_data_inicial', null);
            $pastor->pastoreio_data_final = $request->request->get('pastoreio_data_final', null);
            $pastor->data_ordenacao = $request->request->get('data_ordenacao', null);

            $pastor->push();

            Capsule::commit();

            return $application->json($pastor);
        } catch (Exception $e)
        {
            Capsule::rollback();
            return $application->abort(500, $e->getMessage());
        }
    }
}