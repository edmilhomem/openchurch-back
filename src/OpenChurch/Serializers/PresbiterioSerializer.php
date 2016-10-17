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

class PresbiterioSerializer extends AbstractSerializer
{
    protected $type = 'presbiterios';

    public function getAttributes($model, array $fields = null)
    {
        return [
            'nome' => $model->nome,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }

    public function getLinks($model)
    {
        return ['self' => '/presbiterios/' . $model->id];
    }

}