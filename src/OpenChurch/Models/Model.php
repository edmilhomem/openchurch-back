<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 22/09/2016
 * Time: 00:15
 */

namespace OpenChurch\Models;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection;
use OpenChurch\Serializers\EstadoSerializer;
use OpenChurch\Serializers\IgrejaSerializer;
use OpenChurch\Serializers\PessoaSerializer;
use OpenChurch\Serializers\PresbiterioSerializer;
use OpenChurch\Utils;

abstract class Model
{
    protected $table;
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public static function factory($name, $db)
    {
        $factory = null;
        switch ($name) {
            case 'presbiterio':
                $factory = new Presbiterio($db);
                break;
            case 'igreja':
                $factory = new Igreja($db);
                break;
            case 'estado':
                $factory = new Estado($db);
                break;
            case 'membro':
                $factory = new Membro($db);
                break;
            case 'pessoa':
                $factory = new Pessoa($db);
                break;
        }
        return $factory;
    }

    public static function serializerFactory($name)
    {
        $factory = null;
        switch ($name) {
            case 'presbiterio':
                $factory = new PresbiterioSerializer();
                break;
            case 'igreja':
                $factory = new IgrejaSerializer();
                break;
            case 'estado':
                $factory = new EstadoSerializer();
                break;
            case 'membro':
                //$factory = new Membro();
                break;
            case 'pessoa':
                $factory = new PessoaSerializer();
                break;
        }
        return $factory;
    }

    public function delete(array $identifier)
    {
        return $this->db->delete($this->table, $identifier);
    }

    public function insert($object)
    {
        $object->created_at = Utils::mysqldate();
        $this->db->insert($this->table, (array)$object);
        $object->id = $this->db->lastInsertId();
        return $object;
    }

    public function update($object, array $identifier)
    {
        $object->updated_at = Utils::mysqldate();
        $this->db->update($this->table, (array)$object, $identifier);
        return $object;
    }


    /**
     * Retorna uma ocorrência do model.
     * $with é um array com a estrutura:
     * * [0] pai
     * * [1] pessoas as pais
     * * [2] pessoas.pai_id = pais.id
     *
     * @param array $identifier
     * @param array|null $with
     * @return mixed
     */
    public function find(array $identifier, &$count = 1, array $with = null, $filter = null, $sort = [], $limit = null, $offset = null)
    {
        $select = [];

        $where_params = array_values($identifier);
        $where_params_types = [];
        $where = [];
        foreach (array_keys($identifier) as $field) {
            $where[] = "($this->table.$field = ?)";
        }
        foreach ($where_params as $p) {
            $where_params_types[] = \Doctrine\DBAL\Types\Type::STRING;
        }
        $filter_params = [];
        $filter_params_types = [];
        $w = '';
        if ($filter) {
            $filter = json_decode($filter);
            $w = $this->parseFilter($filter, $filter_params, $filter_params_types);
        }
        if ($w) {
            $where[] = $w;
            $where_params = array_merge($where_params, $filter_params);
            $where_params_types = array_merge($where_params_types, $filter_params_types);
        }
        $joins = [];
        /*
        if ($with) {
            foreach ($with as $related => $join) {
                //$joins[] = "LEFT JOIN $related ON $join";
                //$select[] = "$join.*";
            }
        }
        */
        $select[] = $this->table . ".*";
        $sql = "SELECT count(*) as quantidade "
            . " FROM $this->table "
            . " " . join(" ", $joins);
        if (count($where) > 0) {
            $sql .= " WHERE " . join(" AND ", $where);
        }
        // count
        $query = $this->db->executeQuery($sql, $where_params, $where_params_types);
        $total = $query->fetchObject();
        $count = $total->quantidade;

        // all
        $sql = "SELECT " . join(", ", $select)
            . " FROM $this->table "
            . " " . join(" ", $joins);
        if (count($where) > 0) {
            $sql .= " WHERE " . join(" AND ", $where);
        }

        if ($sort && count($sort) > 0) {
            $sql .= " ORDER BY ";
            $so = [];
            foreach ($sort as $field => $type) {
                $so[] = "$field $type";
            }
            $sql .= join(", ", $so);
        }
        if ($limit && $limit != 'max') {
            $sql .= " LIMIT $limit";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }
        $query = $this->db->executeQuery($sql, $where_params, $where_params_types);
        $o = $query->fetchAll(\PDO::FETCH_OBJ);
        return $o;
    }

    public function findOrNew(array $identifier)
    {
        $o = $this->find($identifier);
        if (!$o) {
            $o = new \stdClass();
        }
        return $o;
    }

    public function exists(array $identifier)
    {
        $wnames = array_keys($identifier);
        $wvalues = array_values($identifier);
        $where = [];
        foreach ($wnames as $field) {
            $where[] = "($field = ?)";
        }
        $sql = "SELECT count(*) as quantidade FROM $this->table WHERE "
            . join(" AND ", $where);
        $query = $this->db->executeQuery($sql, $wvalues);
        $o = $query->fetchObject();
        if ($o) {
            return $o->quantidade > 0;
        } else {
            return false;
        }
    }

    public function page($select, $from, $where, array $params = [])
    {
        $q = Utils::safeProperty($params, 'q'); // critério de busca
        $i = Utils::safeProperty($params, 'i'); // indice da página
        $p = Utils::safeProperty($params, 'p'); // tamanho da página
        $o = Utils::safeProperty($params, 'o', "$this->table.id"); // campo da ordenação
        $t = Utils::safeProperty($params, 't', 'asc'); // tipo da ordenação

        if (!$i || $i <= 0) {
            $i = 1;
        }
        $i--;

        if ($from) $from = "FROM $from";
        if ($where) $where = "WHERE $where";

        $query = $this->db->executeQuery("SELECT count(*) as quantidade $from $where", $q);
        $total = $query->fetchObject();
        $total = $total->quantidade;

        $sql = "SELECT $select $from $where ORDER BY $o $t";
        if ($p) {
            if ($p != -1) {
                $offset = $i * $p;
                $sql .= " LIMIT $p OFFSET $offset";
            }
        }

        $query = $this->db->executeQuery($sql, $q);
        $o = $query->fetchAll(\PDO::FETCH_OBJ);
        return [
            'total' => $total,
            'items' => $o
        ];
    }

    public function select($select = "*", $join = "", $query = "", array $params = null, $mode = 'many')
    {
        $sql = "SELECT $select FROM $this->table $join $query";
        $q = $this->db->executeQuery($sql, $params);
        if ($mode == 'many') {
            return $q->fetchAll();
        } elseif ($mode == 'one') {
            return $q->fetch();
        } else {
            return $q->fetchAll();
        }
    }

    public function save($object, array $identifier)
    {
        if ($this->exists($identifier)) {
            return $this->update($object, $identifier);
        } else {
            return $this->insert($object);
        }
    }


    function parseFilter($filter, array &$params, array &$params_types, $start = true, $top = '', $operator = 'AND')
    {
        $w = '';
        if ($start)
            $w = '(';
        $p = [];
        $i = 0;
        foreach ($filter as $field => $value) {
            $op = "=";
            if (in_array($field, ['$gt', '$gte', '$lt', '$lte', '$in', '$nin', '$like'])) {
                switch ($field) {
                    case '$gt':
                        $op = '>';
                        break;
                    case '$lt':
                        $op = '<';
                        break;
                    case '$like':
                        $op = 'LIKE';
                        $value = "%$value%";
                        break;
                    case '$nin':
                        $op = 'NOT IN';
                        break;
                    case '$in':
                        $op = 'IN';
                        break;
                    case '$gte':
                        $op = '>=';
                        break;
                    case '$lte':
                        $op = '<=';
                        break;
                    default:
                        $op = '=';
                        break;
                }
            }
            if (is_object($value)) {
                if (in_array($field, ['AND', 'OR'])) {
                    $p[] = $this->parseFilter($value, $params, $params_types, true, $top, $field);
                } else {
                    $p[] = $this->parseFilter($value, $params, $params_types, false, $field);
                }
            } else {
                if ($top) {
                    $field = $top;
                }
                if (in_array($op, ['IN', 'NOT IN'])) {
                    $p[] = "$field $op (?)";
                } else {
                    $p[] = "$field $op ?";
                }
                if (is_numeric($value)) {
                    if (is_int($value)) {
                        $params_types[] = \Doctrine\DBAL\Types\Type::INTEGER;
                    }
                    if (is_float($value)) {
                        $params_types[] = \Doctrine\DBAL\Types\Type::FLOAT;
                    }
                }
                if (is_string($value)) {
                    $params_types[] = \Doctrine\DBAL\Types\Type::STRING;
                }
                if (is_bool($value)) {
                    $params_types[] = \Doctrine\DBAL\Types\Type::BOOLEAN;
                }
                if (is_array($value)) {
                    $params_types[] = \Doctrine\DBAL\Connection::PARAM_STR_ARRAY;
                }
                $params[] = $value;
            }
            $i++;
        }

        $w .= join(" $operator ", $p);

        if ($start)
            $w .= ')';
        return $w;
    }
}