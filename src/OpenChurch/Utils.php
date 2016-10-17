<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 20/09/2016
 * Time: 23:45
 */

namespace OpenChurch;


use OpenChurch\Models\Model;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Parameters;
use Tobscure\JsonApi\Resource;

class Utils
{
    public static function safeProperty($o, $prop, $default = null) {
        if (is_array($o)) {
            if (!isset($o[$prop])) {
                return $default;
            } else {
                return $o[$prop];
            }
        } else {
            if (!isset($o->$prop)) {
                return $default;
            } else {
                return $o->$prop;
            }
        }
    }

    public static function safeKey($o, $prop, $default = null) {
        return self::safeProperty($o, $prop, $default);
    }

    public static function mysqldate() {
        return date('Y-m-d H:i:s');
    }

    public static function controller_all_helper($db, $model, $selfUrl, array $include = [], array $sort = []) {
        $manager = Model::factory($model, $db);
        $parameters = new Parameters($_GET);
        $oinclude = $include;
        $include = $parameters->getInclude($include);
        $fields = $parameters->getFields();
        $filter = $parameters->getFilter();
        $sort = $parameters->getSort($sort);
        $limit = $parameters->getLimit(100);
        if (!$limit) $limit = 100;
        $offset = $parameters->getOffset($limit);
        $total = 0;
        $lista = $manager->find([], $total, [], $filter, $sort, $limit, $offset);
        $collection = new Collection($lista, Model::serializerFactory($model));
        $collection->with($include);
        $collection->fields($fields);
        $document = new Document($collection);
        $document->addLink('self', $selfUrl);
        $document->addPaginationLinks('/', [$filter], $offset, $limit, $total);
        $document->addMeta('total', $total);
        return $document;
    }

    public static function controller_find_helper($db, $model, array $identifier = [], array $include = []) {
        $manager = Model::factory($model, $db);
        $parameters = new Parameters($_GET);
        $include = $parameters->getInclude($include);
        $fields = $parameters->getFields();
        $item = $manager->find($identifier);
        if ($item && is_array($item))
            $item = $item[0];
        $resource = new Resource($item, Model::serializerFactory($model));
        $resource->with($include);
        $resource->fields($parameters->getFields());
        $document = new Document($resource);
        return $document;
    }
}