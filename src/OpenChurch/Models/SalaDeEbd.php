<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SalaDeEbd
 * @package OpenChurch\Models
 */
class SalaDeEbd extends Model
{
    protected $table = 'ebd_salas';

    /**
     * Relacionamento Igreja:1-Sala:n
     * @see \OpenChurch\Models\Igreja
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function igreja()
    {
        return $this->belongsTo('OpenChurch\Models\Igreja');
    }

    /**
     * Relacionamento Sala:n-Professor:n
     * @see \OpenChurch\Models\Pessoa
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function professores() {
        return $this->belongsToMany('OpenChurch\Models\Pessoa', 'ebd_professores', 'sala_id', 'pessoa_id')
            ->withTimestamps()
            ->withPivot('data_inicial', 'data_final', 'titular', 'observacoes');
    }

    /**
     * Relacionamento Sala:n-Pessoa:n
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function alunos() {
        return $this->belongsToMany('OpenChurch\Models\Pessoa', 'ebd_matriculas', 'sala_id', 'pessoa_id')
            ->withTimestamps()
            ->withPivot('data_inicial', 'data_final', 'observacoes');
    }

    /**
     * Relacionamento Sala:n-Aula:n
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function aulas() {
        return $this->belongsToMany('OpenChurch\Models\AulaDeEbd', 'ebd_salas_aulas', 'sala_id', 'aula_id')
            ->withTimestamps()
            ->withPivot('assunto', 'quantidade_biblias', 'quantidade_visitantes', 'observacoes', 'pessoa_id');
    }
}