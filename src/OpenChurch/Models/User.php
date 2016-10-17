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

    /**
     * Relacionamento Usuario:n-Igreja:n 
     * Descreve as igrejas que o usuário tem permissão de gerenciar
     *
     * @see \OpenChurch\Models\Igreja 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * @todo Descrever o tipo da permissão
     */
    public function igrejas() {
        return $this->belongsToMany('OpenChurch\Models\Igreja', 'permissoes_usuario_igreja', 'usuario_id', 'igreja_id')
            ->withTimestamps();
            //->withPivot('assunto', 'quantidade_biblias', 'quantidade_visitantes', 'observacoes', 'pessoa_id');
    }
}