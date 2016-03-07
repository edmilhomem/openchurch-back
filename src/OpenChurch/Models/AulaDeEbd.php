<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

use Illuminate\Database\Eloquent\Model;

class AulaDeEbd extends Model
{
    protected $table = 'ebd_aulas';

    public function igreja() {
        return $this->belongsTo('OpenChurch\Models\Igreja');
    }

    public function presentes() {
        return $this->belongsToMany('OpenChurch\Models\Pessoa', 'ebd_presencas', 'aula_id', 'pessoa_id')
            ->withTimestamps()
            ->withPivot('observacoes','situacao');
    }

}