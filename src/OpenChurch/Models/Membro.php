<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

use Illuminate\Database\Eloquent\Model;

class Membro extends Model
{
    protected $table = 'membros';

    public function pessoa()
    {
        return $this->belongsTo('OpenChurch\Models\Pessoa');
    }

    public function igreja()
    {
        return $this->belongsTo('OpenChurch\Models\Igreja');
    }

}