<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 17:21
 */

namespace OpenChurch\Controllers;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use OpenChurch\Utils;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    public function all(Application $application, Request $request)
    {
        $document = Utils::controller_all_helper($application['db'], 'igreja', '/igrejas',
            ['presbiterio'],
            ['id', 'nome', 'sigla', 'created_at', 'updated_at']);
        return $application->json($document);
    }

    /**
     * Encontra uma igreja com base no identificador (id). Dispara exceção (404) se uma igreja não for
     * encontrada com o identificador informado.
     *
     * @param $id
     * @param Application $application
     * @return \Symfony\Component\HttpFoundation\JsonResponse|void
     *
     * @throws
     */
    public function find($id, Application $app)
    {
        $document = Utils::controller_find_helper($app['db'], 'igreja',
            ['id' => $id], ['id', 'nome', 'presbiterio', 'created_at', 'updated_at', 'presbiterio']);
        return $app->json($document);
    }

    public function exists($id, Application $app)
    {
        $sql = 'SELECT id FROM igrejas WHERE id = ?';
        $query = $app['db']->executeQuery($sql, array($id));
        $igreja = $query->fetch();
        if ($igreja) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Salva (cadastra e atualiza) os dados de uma igreja. Se uma igreja for encontrada com base no parâmetro
     * id, então está no modo de atualização. Caso contrário, está no modo de cadastro (nova igreja).
     *
     * Os seguintes dados são esperados via POST:
     * * nome
     * * presbiterio_id
     * * endereco
     * * endereco_numero
     * * endereco_bairro
     * * endereco_cidade
     * * endereco_uf
     * * endereco_cep
     * * telefone
     * * email
     *
     * @param $id
     * @param Application $application
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|void
     */
    public function save($id = 0, Application $app, Request $request)
    {
        $post = json_decode($request->getContent());
        $igreja = new \stdClass();
        $existe = false;
        if ($id) {
            $query = $app['db']->executeQuery('SELECT * FROM igrejas WHERE id = ?', array($id));
            $igreja = $query->fetchObject();
            if ($igreja) {
                $existe = true;
            } else {
                $existe = false;
            }
        }

        $igreja->nome = Utils::safeProperty($post, 'nome');
        $igreja->slug = $app['slugify']->slugify($igreja->nome);
        $igreja->presbiterio_id = Utils::safeProperty($post, 'presbiterio_id');
        $igreja->endereco = Utils::safeProperty($post, 'endereco');
        $igreja->endereco_numero = Utils::safeProperty($post, 'endereco_numero');
        $igreja->endereco_bairro = Utils::safeProperty($post, 'endereco_bairro');
        $igreja->endereco_cidade = Utils::safeProperty($post, 'endereco_cidade');
        $igreja->endereco_uf = Utils::safeProperty($post, 'endereco_uf');
        $igreja->endereco_cep = Utils::safeProperty($post, 'endereco_cep');
        $igreja->telefone = Utils::safeProperty($post, 'telefone');
        $igreja->email = Utils::safeProperty($post, 'email');

        if ($existe) {
            $igreja->updated_at = Utils::mysqldate();
            $app['db']->update('igrejas', (array)$igreja, ['id' => $id]);
        } else {
            $igreja->created_at = Utils::mysqldate();
            $app['db']->insert('igrejas', (array)$igreja);
            $igreja->id = $app['db']->lastInsertId();
        }

        return $app->json(['data' => $igreja]);
    }

    public function delete($id, Application $app)
    {
        if (!$this->exists($id, $app)) {
            throw new NotFoundHttpException("A igreja (id = $id) não foi encontrada.");
        }

        $sql = 'DELETE FROM igrejas WHERE id = ?';
        try {
            $r = $app['db']->executeUpdate($sql, array($id));
            if ($r > 0) {
                return $app->json(['data' => 'ok']);
            } else {
                throw new HttpException(500, "Não foi possível excluir a igreja (id = $id).");
            }
        } catch (Exception $e) {
            throw new HttpException(500, "Erro interno. Não foi possível excluir a igreja (id = $id).", $e);
        } catch (DBALException $e) {
            throw new HttpException(500, "Erro no banco de dados. Não foi possível excluir a igreja (id = $id).", $e);
        }
    }


    public function all_aulas($id, $ano = null, Application $application, Request $request)
    {
        if (!$this->exists($id, $application)) {
            throw new NotFoundHttpException("A igreja (id = $id) não foi encontrada.");
        }
        $q = $request->query->get('q', null); // critério de busca
        $i = $request->query->get('i', null); // indice da página
        $p = $request->query->get('p', null); // tamanho da página
        $o = $request->query->get('o', 'data'); // campo da ordenação
        $t = $request->query->get('t', 'asc'); // tipo da ordenação

        if (!$i || $i <= 0) {
            $i = 1;
        }
        $i--;

        $select = "SELECT * ";
        $sql = "FROM ebd_aulas ";
        $where = [];
        $where_params = [];
        if ($ano) {
            $where[] = "(year(data) = ?) AND ";
        }

        if ($q) {
            $q = "%$q%";
            $where[] = "(data like ?) OR ";
            $where[] = "(observacoes like ?)";
        }

        if (count($where)) {
            $sql .= ' WHERE ' . join("", $where);
            if ($ano) {
                $where_params[] = $ano;
            }
            if ($q) {
                $where_params[] = $q;
                $where_params[] = $q;
            }
        }
        $query = $application['db']->executeQuery("SELECT count(*) as quantidade " . $sql, $where_params);
        $total = $query->fetch();
        $total = $total['quantidade'];

        $sql .= " ORDER BY $o $t";

        if ($p) {
            $offset = $i * $p;
            $sql .= " LIMIT $p OFFSET $offset";
        }

        $query = $application['db']->executeQuery($select . $sql, $where_params);
        $aulas = $query->fetchAll();

        return $application->json(
            array(
                'data' => [
                    'total' => $total,
                    'items' => $aulas
                ]
            )
        );
    }

    public function save_aulas($id, $idAula = null, Application $application, Request $request)
    {
        if (!$this->exists($id, $application)) {
            throw new NotFoundHttpException("A igreja (id = $id) não foi encontrada.");
        }
        $post = json_decode($request->getContent());
        $aula = new \stdClass();
        $existe = false;
        if ($idAula) {
            $query = $application['db']->executeQuery('SELECT * FROM ebd_aulas WHERE id = ?', array($idAula));
            $aula = $query->fetchObject();
            if ($aula) {
                $existe = true;
            } else {
                $existe = false;
            }
        }
        $aula->igreja_id = $id;
        $aula->data = Utils::safeProperty($post, 'data');
        $aula->observacoes = Utils::safeProperty($post, 'observacoes');
        if ($existe) {
            $aula->updated_at = Utils::mysqldate();
            $application['db']->update('ebd_aulas', (array)$aula, ['id' => $idAula]);
        } else {
            $aula->created_at = Utils::mysqldate();
            $application['db']->insert('ebd_aulas', (array)$aula);
            $aula->id = $application['db']->lastInsertId();
        }
        return $application->json(['data' => $aula]);
    }

    public function find_aula($id, $idAula, Application $application, Request $request)
    {
        if (!$this->exists($id, $application)) {
            throw new NotFoundHttpException("A igreja (id = $id) não foi encontrada.");
        }
        $query = $application['db']->executeQuery("SELECT * FROM ebd_aulas WHERE igreja_id = ? AND id = ?",
            array($id, $idAula));
        $aula = $query->fetch();
        if (!$aula)
            throw new NotFoundHttpException("A aula (id = $idAula) não foi encontrada.");
        return $application->json(["data" => $aula]);
    }

    public function config_aulas($id, $ano = null, Application $application, Request $request)
    {
        if (!$ano) $ano = (integer)date('Y');
        if (!$this->exists($id, $application)) {
            throw new NotFoundHttpException("A igreja (id = $id) não foi encontrada.");
        }
        $query = $application['db']->executeQuery("SELECT count(*) as quantidade FROM ebd_aulas WHERE year(data) = ?",
            array($ano));
        $total_aulas = $query->fetch();
        $total_aulas = $total_aulas['quantidade'];
        if ($total_aulas > 0) {
            throw new HttpException(500, "Já existem $total_aulas aula(s) cadastrada(s) para $ano.");
        }
        $primeiro_domingo = new \DateTime(date('Y-m-d', strtotime('first sunday of January ' . $ano)));
        $ultimo_domingo = new \DateTime(date('Y-m-d', strtotime('last sunday of December ' . $ano)));
        $data = $primeiro_domingo;
        $datas = array(clone($data));
        while ($data->diff($ultimo_domingo)->days > 0) {
            $data->add(date_interval_create_from_date_string('7 days'));
            $datas[] = clone($data);
        }
        try {
            $application['db']->beginTransaction();
            foreach ($datas as $data) {
                $aula = new \stdClass();
                $aula->igreja_id = $id;
                $aula->data = $data->format('Y-m-d');
                $aula->created_at = Utils::mysqldate();
                $application['db']->insert('ebd_aulas', (array)$aula);
            }
            $application['db']->commit();
        } catch (Exception $e) {
            $application['db']->rollBack();
            throw new Exception("Ocorreu erro ao criar as aulas para o ano $ano", $e);
        }
        return $application->json(['data' => count($datas)]);
    }

    public function estatisticas($id, Application $application)
    {
        if (!$this->exists($id, $application)) {
            throw new NotFoundHttpException("A igreja (id = $id) não foi encontrada.");
        }

        $sql_membros = "SELECT count(*) as quantidade 
FROM membros INNER JOIN pessoas on membros.pessoa_id = pessoas.id 
WHERE membros.igreja_id = ?";

        $sql_homens = $sql_membros . " AND pessoas.sexo = 'M'";
        $sql_mulheres = $sql_membros . " AND pessoas.sexo = 'F'";

        $query = $application['db']->executeQuery($sql_membros, array($id));
        $membros = $query->fetch();
        $membros = $membros['quantidade'];

        $query = $application['db']->executeQuery($sql_homens, array($id));
        $homens = $query->fetch();
        $homens = $homens['quantidade'];

        $query = $application['db']->executeQuery($sql_mulheres, array($id));
        $mulheres = $query->fetch();
        $mulheres = $mulheres['quantidade'];

        /*
         * select count(*) as quantidade, round(datediff(now(), pessoas.data_de_nascimento)/365) as idade
from membros inner join pessoas on pessoas.id = membros.pessoa_id
where igreja_id=1
group by idade;

         */

        $sql_membros_por_idade = "SELECT count(*) as quantidade, round(datediff(now(), pessoas.data_de_nascimento)/365) as idade 
FROM membros INNER JOIN pessoas on membros.pessoa_id = pessoas.id 
WHERE membros.igreja_id = ? 
GROUP BY idade";
        $query = $application['db']->executeQuery($sql_membros_por_idade, array($id));
        $membros_por_idade = $query->fetchAll();

        $sql_membros_por_faixa_etaria = "SELECT count(*) as quantidade, round(datediff(now(), pessoas.data_de_nascimento)/365) div 10 as faixa_etaria
FROM membros INNER JOIN pessoas on membros.pessoa_id = pessoas.id 
WHERE membros.igreja_id = ? 
GROUP BY faixa_etaria";
        $query = $application['db']->executeQuery($sql_membros_por_faixa_etaria, array($id));
        $membros_por_faixa_etaria = $query->fetchAll();


        $sql_aniversariantes = "SELECT membros.id as membro_id, pessoas.*, round(datediff(now(), pessoas.data_de_nascimento)/365) as idade 
FROM membros INNER JOIN pessoas ON membros.pessoa_id = pessoas.id
WHERE membros.igreja_id = ? AND month(pessoas.data_de_nascimento)=month(now())";
        $query = $application['db']->executeQuery($sql_aniversariantes, array($id));
        $aniversariantes = $query->fetchAll();

        return $application->json(array(
            'data' => [
                'membros' => $membros,
                'homens' => $homens,
                'mulheres' => $mulheres,
                'membros_por_idade' => $membros_por_idade,
                'membros_por_faixa_etaria' => $membros_por_faixa_etaria,
                'aniversariantes' => $aniversariantes
            ]
        ));
    }
}