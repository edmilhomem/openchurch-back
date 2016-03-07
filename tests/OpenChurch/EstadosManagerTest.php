<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 23/12/2015
 * Time: 11:12
 */

namespace OpenChurch;

use OpenChurch\Managers\EstadosManager;


class EstadosManagerTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $em;
    private $manager;

    public function setup() {
        require __DIR__.'/../../phpunit.bootstrap.php';
        $this->em = $em;
        $this->manager = new EstadosManager($this->em);
    }

    public function testCreate() {
        $dados = array(
            'nome' => 'Tocantins',
            'sigla' => 'TO'
        );
        $estado = $this->manager->create($dados);
    }

    /**
     * @expectedException   Exception
     */
    public function testCreateWithException() {
        $dados = array(
            'nome' => 'Tocantins',
            'sigla' => 'TO'
        );
        $estado = $this->manager->create($dados);
    }

    public function testUpdate() {
        $dados = array(
            'id' => 1,
            'nome' => 'Tocantins',
            'sigla' => 'TO'
        );
        $estado = $this->manager->update($dados);
    }

    public function testRemove() {
        $this->manager->remove(1);
    }
}