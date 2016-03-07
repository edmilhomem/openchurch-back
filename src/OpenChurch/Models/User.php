<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 02:22
 */

namespace OpenChurch\Models;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $hidden = ['password', 'salt', 'confirmation_token', 'password_reset_request_date'];

    public function getRolesAttribute($value)
    {
        return explode(',', $value);
    }

    public function setRolesAttribute($value)
    {
        $this->attributes['roles'] = implode(',', $value);
    }
}