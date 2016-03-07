<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 23/12/2015
 * Time: 10:30
 */

namespace OpenChurch;


class QueryParameterParser
{
    public static function parse($input) {
        if (is_array($input)) {
            $r = array();
            foreach($input as $i) {
                $r[] = self::parse($i);
            }
            return $r;
        }

        $templates = array(
            'eq'    =>  "%s = ?",
            'like'  =>  "%s LIKE ?"
        );
        $re = "/^(.*?)__(.*?):(.*?)$/";
        preg_match($re, $input, $matches);
        $lexpr = $matches[1];
        $op = $matches[2];
        $rexpr = $matches[3];

        $o = sprintf($templates[$op], $lexpr);

        $p = array(
            'query' => $o,
            'param' => $rexpr,
            'operator' => $op
        );

        return $p;
    }

    public static function buildWhere($conditions, $op = 'OR') {
        $dql = " (";
        $params = array();
        $i = 1;
        foreach($conditions as $c) {
            $params[] = str_replace("?", "?".$i, $c['query']);
            $i++;
        }
        $dql .= join(" $op ", $params) . ")";
        return $dql;
    }

    public static function buildParameters($conditions) {
        $parameters = array();
        $i = 1;
        foreach($conditions as $c) {
            $parameters[$i] = ($c['operator'] == 'like' ? '%'.$c['param'].'%' : $c['param']);
            $i++;
        }
        return $parameters;
    }
}