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
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

class PessoasController
{
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

        $query = Pessoa::with(array('pai', 'mae', 'conjuge'));

        if ($q) {
            $q = "%$q%";
            $query->where('nome', 'like', $q);
        }

        $total = $query->count();
        $query->orderBy($o, $t);
        if ($skip !== null) {
            $query->skip($skip)->take($p);
        }
        $pessoas = $query->get();

        return $application->json(
            array(
                'total' => $total,
                'items' => $pessoas
            )
        );
    }

    public function find($id, Application $application)
    {
        $pessoa = Pessoa::with('pai', 'mae', 'conjuge')->findOrFail($id);
        return $application->json($pessoa);
    }

    public function save($id = null, Application $application, Request $request)
    {
        //dd($request->request->get('pai')['nome']);

        $pessoa = Pessoa::findOrNew($id);

        // tenta encontrar pai, mae, conjuge
        if ($request->request->get('pai', null)) {
            $pai = Pessoa::findOrNew($request->request->get('pai')['id']);
            $pai->nome = DataUtils::array_key($request->request->get('pai'), 'nome');
            $pai->religiao = DataUtils::array_key($request->request->get('pai'), 'religiao');
            if ($request->request->get('pai')['id'] == null) {
                $pai->save();
            }
            $pessoa->pai()->associate($pai);
        } else {
            $pessoa->pai_id = null;
        }

        if ($request->request->get('mae', null)) {
            $mae = Pessoa::findOrNew($request->request->get('mae')['id']);
            $mae->nome = DataUtils::array_key($request->request->get('mae'), 'nome');
            $mae->religiao = DataUtils::array_key($request->request->get('mae'), 'religiao');
            if ($request->request->get('mae')['id'] == null) {
                $mae->save();
            }
            $pessoa->mae()->associate($mae);
        } else {
            $pessoa->mae_id = null;
        }

        if ($request->request->get('conjuge', null)) {
            $conjuge = Pessoa::findOrNew($request->request->get('conjuge')['id']);
            $conjuge->nome = DataUtils::array_key($request->request->get('conjuge'), 'nome');
            $conjuge->religiao = DataUtils::array_key($request->request->get('conjuge'), 'religiao');
            if ($request->request->get('conjuge')['id'] == null) {
                $conjuge->save();
            }
            $pessoa->conjuge()->associate($conjuge);
        } else {
            $pessoa->conjuge_id = null;
        }

        $pessoa->cpf = $request->request->get('cpf', null);
        $pessoa->data_de_nascimento = $request->request->get('data_de_nascimento', null);
        $pessoa->email = $request->request->get('email', null);
        $pessoa->endereco = $request->request->get('endereco', null);
        $pessoa->endereco_numero = $request->request->get('endereco_numero', null);
        $pessoa->endereco_bairro = $request->request->get('endereco_bairro', null);
        $pessoa->endereco_cidade = $request->request->get('endereco_cidade', null);
        $pessoa->endereco_uf = $request->request->get('endereco_uf', null);
        $pessoa->endereco_cep = $request->request->get('endereco_cep', null);
        $pessoa->estado_civil = $request->request->get('estado_civil', null);
        $pessoa->instrucao = $request->request->get('instrucao', null);
        $pessoa->nacionalidade = $request->request->get('nacionalidade', null);
        $pessoa->naturalidade_cidade = $request->request->get('naturalidade_cidade', null);
        $pessoa->naturalidade_uf = $request->request->get('naturalidade_uf', null);
        $pessoa->nome = $request->request->get('nome', null);
        if (!$pessoa->nome) {
            throw new Exception('O nome deve ser obrigatoriamente informado');
        }
        $pessoa->observacoes = $request->request->get('observacoes', null);
        $pessoa->profissao = $request->request->get('profissao', null);
        $pessoa->religiao = $request->request->get('religiao', null);
        $pessoa->sexo = $request->request->get('sexo', null);
        $pessoa->telefone = $request->request->get('telefone', null);

        $pessoa->push();

        return $application->json($pessoa);
    }
}