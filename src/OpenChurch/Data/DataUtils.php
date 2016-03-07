<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 02/01/2016
 * Time: 02:30
 */

namespace OpenChurch\Data;

use Symfony\Component\Security\Acl\Exception\Exception;

class DataUtils
{
    public static function array_key($array, $key, $default = null) {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }
    public static function str2date($str, $format = 'Y-m-d') {
        try {
            return new \DateTime($str);
        } catch (Exception $e) {
            return null;
        }
    }
}