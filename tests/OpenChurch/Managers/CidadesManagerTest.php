<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 24/12/2015
 * Time: 02:22
 */

namespace OpenChurch\Tests\Managers;

use OpenChurch\Managers\CidadesManager;
use OpenChurch\Managers\EstadosManager;
use OpenChurch\ORM\Mapping\Cidade;
use OpenChurch\ORM\Mapping\Estado;

class CidadesManagerTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $em;
    private $manager;

    public function setup() {
        require __DIR__.'/../../../phpunit.bootstrap.php';
        $this->em = $em;
        $this->manager = new CidadesManager($this->em);
    }

    public function testCreate() {
        $estado = (new EstadosManager($this->em))->find(array('estado.nome__eq:Tocantins'));
        $dados = array(
            'nome' => 'Paraíso do Tocantins',
            'estado_id' => $estado->getId()
        );
        $cidade = $this->manager->create($dados);
        print_r($cidade);
    }
}
