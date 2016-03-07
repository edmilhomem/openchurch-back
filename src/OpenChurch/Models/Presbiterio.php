<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 16:21
 */

namespace OpenChurch\Models;
use Illuminate\Database\Eloquent\Model;

class Presbiterio extends Model
{
    protected $table = 'presbiterios';

    public function igrejas()
    {
        return $this->hasMany('OpenChurch\Models\Igreja');
    }
}