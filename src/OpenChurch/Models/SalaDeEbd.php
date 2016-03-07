<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

use Illuminate\Database\Eloquent\Model;

class SalaDeEbd extends Model
{
    protected $table = 'ebd_salas';

    public function igreja()
    {
        return $this->belongsTo('OpenChurch\Models\Igreja');
    }

    public function professores() {
        return $this->belongsToMany('OpenChurch\Models\Pessoa', 'ebd_professores', 'sala_id', 'pessoa_id')
            ->withTimestamps()
            ->withPivot('data_inicial', 'data_final', 'titular', 'observacoes');
    }

    public function alunos() {
        return $this->belongsToMany('OpenChurch\Models\Pessoa', 'ebd_matriculas', 'sala_id', 'pessoa_id')
            ->withTimestamps()
            ->withPivot('data_inicial', 'data_final', 'observacoes');
    }

    public function aulas() {
        return $this->belongsToMany('OpenChurch\Models\AulaDeEbd', 'ebd_salas_aulas', 'sala_id', 'aula_id')
            ->withTimestamps()
            ->withPivot('assunto', 'quantidade_biblias', 'quantidade_visitantes', 'observacoes', 'pessoa_id');
    }
}