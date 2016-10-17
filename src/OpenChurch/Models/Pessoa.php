<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 05/01/2016
 * Time: 01:15
 */

namespace OpenChurch\Models;

class Pessoa extends Model
{
    protected $table = 'pessoas';

    public function find(array $identifier, &$count = 1, array $with = null, $filter = null, $sort = [], $limit = null, $offset = null) {
        $p = parent::find($identifier, $count, $with, $filter, $sort, $limit, $offset);
        if ($p) {
            $ids = [];
            foreach ($p as $pessoa) {
                if ($pessoa->pai_id) $ids[] = $pessoa->pai_id;
                if ($pessoa->mae_id) $ids[] = $pessoa->mae_id;
                if ($pessoa->conjuge_id) $ids[] = $pessoa->conjuge_id;
            }
            $c = 0;
            $related = parent::find([], $c, [], '{"id":{"$in":[' . join(',',$ids) . ']}}');
            foreach ($p as $pessoa) {
                foreach ($related as $r) {
                    if ($pessoa->pai_id && $r->id == $pessoa->pai_id)
                        $pessoa->pai = $r;
                    if ($pessoa->mae_id && $r->id == $pessoa->mae_id)
                        $pessoa->mae = $r;
                    if ($pessoa->conjuge_id && $r->id == $pessoa->conjuge_id)
                        $pessoa->conjuge = $r;
                }
            }
        }
        return $p;
    }
}