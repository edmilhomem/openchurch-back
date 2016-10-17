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
use OpenChurch\Serializers\PessoaSerializer;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Parameters;
use Tobscure\JsonApi\Resource;

class PessoasController
{
    public function all(Application $application, Request $request)
    {
        $manager = new Pessoa($application['db']);
        $parameters = new Parameters($_GET);
        $include = $parameters->getInclude(['conjuge', 'pai', 'mae']);
        $fields = $parameters->getFields();
        $filter = $parameters->getFilter();
        $sort = $parameters->getSort(['id', 'nome', 'created_at', 'updated_at', 'data_de_nascimento']);
        $limit = $parameters->getLimit(100);
        if (!$limit) $limit = 100;
        $offset = $parameters->getOffset($limit);

        $pessoas = $manager->find([], $total, [], $filter, $sort, $limit, $offset);
        $collection = (new Collection($pessoas, new PessoaSerializer));
        $collection->with($include);
        $collection->fields($fields);
        $document = new Document($collection);
        $document->addLink('self', '/pessoas');
        $document->addPaginationLinks('/', [$filter], $offset, $limit, $total);
        $document->addMeta('total', $total);
        return $application->json($document);
    }

    public function find($id, Application $application)
    {
        $parameters = new Parameters($_GET);
        $include = $parameters->getInclude(['pai', 'mae', 'conjuge']);

        $pessoas_manager = new Pessoa($application['db']);
        $total = 1;
        $pessoa = $pessoas_manager->find(['id' => $id], $total, $include);

        $resource = new Resource($pessoa, new PessoaSerializer);
        $resource->with($include);
        $resource->fields($parameters->getFields());
        $document = new Document($resource);

        return $application->json($document);
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