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

class IgrejaSerializer extends AbstractSerializer
{
    protected $type = 'igrejas';

    public function getAttributes($model, array $fields = null)
    {
        return [
            'nome' => $model->nome,
            'slug' => $model->slug,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
            'presbiterio_id' => $model->presbiterio_id
        ];
    }

    public function getLinks($model)
    {
        return ['self' => '/igrejas/' . $model->id];
    }

    public function presbiterio($model) {
        if (isset($model->presbiterio) && ($model->presbiterio)) {
            $presbiterio = new Resource($model->presbiterio, new PresbiterioSerializer());
            return new Relationship($presbiterio);
        } else {
            return null;
        }
    }
}