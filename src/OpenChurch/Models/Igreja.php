<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 16:05
 */

namespace OpenChurch\Models;
use Illuminate\Database\Eloquent\Model;

class Igreja extends Model
{
    protected $table = 'igrejas';

    public function presbiterio()
    {
        return $this->belongsTo('OpenChurch\Models\Presbiterio');
    }

    public function membros() {
        return $this->hasMany('OpenChurch\Models\Membro');
    }

    public function pastores() {
        return $this->hasMany('OpenChurch\Models\Pastor');
    }
}