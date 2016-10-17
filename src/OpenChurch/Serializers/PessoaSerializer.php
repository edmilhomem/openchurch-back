<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 24/09/2016
 * Time: 14:10
 */

namespace OpenChurch\Serializers;


use Tobscure\JsonApi\AbstractSerializer;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

class PessoaSerializer extends AbstractSerializer
{
    protected $type = 'pessoas';
    public function getAttributes($model, array $fields = null)
    {
        return [
            'nome' => $model->nome,
            'data_de_nascimento' => $model->data_de_nascimento,
            'conjuge_id' => $model->conjuge_id,
            'pai_id' => $model->pai_id,
            'mae_id' => $model->mae_id,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
    public function getLinks($model) {
        return ['self' => '/pessoas/' . $model->id];
    }
    public function conjuge($model) {
        if (isset($model->conjuge) && ($model->conjuge)) {
            $conjuge = new Resource($model->conjuge, new PessoaSerializer());
            return new Relationship($conjuge);
        } else {
            return null;
        }
    }
    public function pai($model) {
        if (isset($model->pai) && ($model->pai)) {
            $pai = new Resource($model->pai, new PessoaSerializer());
            return new Relationship($pai);
        } else {
            return null;
        }
    }
    public function mae($model) {
        if (isset($model->mae) && ($model->mae)) {
            $mae = new Resource($model->mae, new PessoaSerializer());
            return new Relationship($mae);
        } else {
            return null;
        }
    }
}