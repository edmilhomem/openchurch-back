<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

use Illuminate\Database\Eloquent\Model;

class PresencaDeEbd extends Model
{
    protected $table = 'ebd_presencas';

    public function aula()
    {
        return $this->belongsTo('OpenChurch\Models\AulaDeEbd');
    }
    public function pessoa() {
        return $this->belongsTo('OpenChurch\Models\Pessoa');
    }
}