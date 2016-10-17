<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 04/01/2016
 * Time: 16:05
 */

namespace OpenChurch\Models;

class Igreja extends Model
{
    protected $table = 'igrejas';

    public function find(array $identifier, &$count = 1, array $with = null, $filter = null, $sort = [], $limit = null, $offset = null) {
        $items = parent::find($identifier, $count, $with, $filter, $sort, $limit, $offset);
        if ($items && is_array($items)) {
            $ids = [];
            foreach ($items as $item) {
                if ($item->presbiterio_id) $ids[] = $item->presbiterio_id;
            }
            $c = 0;
            $related = Model::factory('presbiterio', $this->db)->find([], $c, [], '{"id":{"$in":[' . join(',', $ids) . ']}}');
            foreach ($items as $item) {
                foreach ($related as $r) {
                    if ($item->presbiterio_id && $r->id == $item->presbiterio_id)
                        $item->presbiterio = $r;
                }
            }
        }
        return $items;
    }

}