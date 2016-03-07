<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    protected $table = 'pessoas';

    public function pai()
    {
        return $this->belongsTo('OpenChurch\Models\Pessoa', 'pai_id', 'id');
    }

    public function mae()
    {
        return $this->belongsTo('OpenChurch\Models\Pessoa', 'mae_id', 'id');
    }

    public function conjuge()
    {
        return $this->belongsTo('OpenChurch\Models\Pessoa', 'conjuge_id', 'id');
    }

}