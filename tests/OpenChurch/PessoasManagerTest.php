<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 22/12/2015
 * Time: 11:19
 */

namespace OpenChurch\Tests;


use OpenChurch\Managers\CidadesManager;
use OpenChurch\Managers\PessoasManager;

class PessoasManagerTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $em;
    private $manager;

    public function setup() {
        require __DIR__.'/../../phpunit.bootstrap.php';
        $this->em = $em;
        $this->manager = new PessoasManager($this->em);
    }

    public function testCreate() {
        $dados = array(
            'nome' => 'José da Silva',
            'pai_nome' => 'João da Silva',
            'mae_nome' => 'Jane da Silva',
            'conjuge_nome' => 'Maria da Silva'
        );
        $pessoa = $this->manager->create($dados);
    }

    public function testCreateCompleto() {
        $naturalidade = (new CidadesManager($this->em))->findByNameAndUf('Paraíso do Tocantins', 'TO');
        $cidade = (new CidadesManager($this->em))->findByNameAndUf('Paraíso do Tocantins', 'TO');

        $dados = array(
            'nome' => 'Paulo Roberto',
            'pai_nome' => 'João da Silva',
            'mae_nome' => 'Jane da Silva',
            'naturalidade_id' => $naturalidade->getId(),
            'cidade_id' => $cidade->getId(),
            'sexo' => 'M',
            'estado_civil' => 'solteiro',
            'profissao' => 'Autônomo',
            'data_de_nascimento' => date_create_from_format('Y-m-d', '1980-10-01'),
            'religiao' => 'Evangélico',
            'religiao_anterior' => 'Evangélico',
            'endereco' => 'Rua 1, 1000',
            'cep' => '77.600-000',
            'telefone' => '(63) 3602-1000',
            'email' => 'pauloroberto@gmail.com',
            'observacoes' => 'Vindo de Palmas',
            'cpf' => '111.111.111-11'
        );

        $pessoa = $this->manager->create($dados);
    }

    public function testFindAllNoneFound() {
        $pessoas = $this->manager->findAll(array('pessoa.nome__eq:admin'));
        $this->assertTrue(count($pessoas) == 0);
    }

    public function testFindAllMoreThanOneResult() {
        $pessoas = $this->manager->findAll(array('pessoa.nome__eq:José da Silva','pessoa.sexo__eq:M'));
        $this->assertTrue(count($pessoas) > 0);
    }

    public function testFindNoneFound() {
        $pessoa = $this->manager->find(array('pessoa.nome__eq:admin'));
        $this->assertNull($pessoa);
    }

    public function testFind() {
        $pessoa = $this->manager->find(array('pessoa.nome__eq:José da Silva'));
        $this->assertNotNull($pessoa);
    }

    public function testRemoveById() {
        $pessoa = $this->manager->find(array('pessoa.nome__eq:José da Silva'));
        $this->manager->remove($pessoa->getId());
    }

    public function testRemoveAll() {
        $this->manager->removeAll();
    }

    public function testUpdate() {
        $dados = array(
            'id' => 4,
            'nome' => 'José da Silva',
            'pai' => array(
                'id' => 1
            ),
            'mae' => array(
                'id' => 6
            ),
            'conjuge' => array(
                'id' => 7
            ),
            'nacionalidade' => 'Brasileira',
            'sexo' => 'M',
            'estado_civil' => 'Solteiro',
            'profissao' => 'Professor',
            'religiao' => 'Católico',
            'endereco' => 'Rua Piauí',
            'endereco_numero' => 665,
            'endereco_bairro' => 'Setor Oeste',
            'endereco_cidade' => 'Paraíso do Tocantins',
            'endereco_uf' => 'TO',
            'endereco_cep' => '77600-000',
            'telefone' => '9999-9999',
            'email' => 'jose@gmail.com',
            'cpf' => '111.111.111-11',
            'instrucao' => 'Completo',
            'naturalidade_cidade' => 'Paraíso do Tocantins',
            'naturalidade_uf' => 'TO'
        );

        $pessoa = $this->manager->update($dados);
    }
}
