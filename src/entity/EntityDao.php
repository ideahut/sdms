<?php
namespace Ideahut\sdms\entity;

use Exception;
use ReflectionClass;
use ReflectionProperty;

use \Ideahut\sdms\base\BaseApp;
use \Ideahut\sdms\object\Page;


class EntityDao
{

    // Rules Condition
    const LIKE          = "like";
    const START         = "start";
    const END           = "end";
    const EQUAL         = "eq";
    const BETWEEN       = "between";
    const NOT_NULL      = "notnull";
    const IS_NULL       = "isnull";
    const GREATER_THAN  = "gt";
    const GREATER_EQUAL = "ge";
    const LESS_THAN     = "lt";
    const LESS_EQUAL    = "le";
    const IN            = "in";

    private static $RULES = [
        self::LIKE,
        self::START,
        self::END,
        self::EQUAL,
        self::BETWEEN,
        self::NOT_NULL,
        self::IS_NULL,
        self::GREATER_THAN,
        self::GREATER_EQUAL,
        self::LESS_THAN,
        self::LESS_EQUAL,
        self::IN
    ];

    private static $DEBUG = true;

    private static $DELIMITER = "__"; 


    private $manager;

	private $class;

    private $logger;

    private $pk_value;

    private $pk_field;

    private $page;

    private $start;

    private $limit;

    private $filter = [];

    private $order;

    private $group;    

    private $field;

    private $value;

    

    private $soft_delete = false;

    private $has_time = false;

    private $ref_class;


	public function __construct() {
    	$argv = func_get_args();
    	$narg = func_num_args();
        if ($narg > 0) {
    		$this->manager($argv[0]);
    	}
    	if ($narg > 1) {
    		$this->entity_class($argv[1]);
    	}
        if ($narg > 2) {
            $this->logger($argv[2]);
        }
    }

    public function manager() {
        if (func_num_args() === 0) {
            return $this->manager;
        }
        $manager = func_get_args()[0];
        if ($manager instanceof BaseApp) {
            $this->logger = $manager->getLogger();
            $manager = $manager->getEntityManager();            
        }
        $this->manager = $manager;
        $this->pk_name(1);
        return $this;
    }

    public function entity_class() {
        if (func_num_args() === 0) {
            return $this->class;
        }
        $this->class = func_get_args()[0];
        $this->pk_name(1);
        $this->soft_delete = is_subclass_of($this->class, EntitySoftDelete::class);
        $this->has_time    = is_subclass_of($this->class, EntityTime::class);
        $this->ref_class   = new ReflectionClass($this->class);
        return $this;
    }

    public function logger() {
        if (func_num_args() === 0) {
            return $this->logger;
        }
        $this->logger = func_get_args()[0];
        return $this;
    }

    public function pk_name() {
        if (func_num_args() === 0) {
            return $this->pk_field;
        }
        if ($this->manager !== null && $this->class !== null) {
            $meta = $this->manager->getClassMetadata($this->class);
            $this->pk_field = $meta->getSingleIdentifierFieldName();
        }
        return $this;
    }

    public function pk() {
        if (func_num_args() === 0) {
            return $this->pk_value;
        }
        $this->pk_value = func_get_args()[0];
        return $this;
    }

    public function page() {
        if (func_num_args() === 0) {
            return $this->page;
        }
        $page = func_get_args()[0];
        if ($page instanceof Page) {
            $this->page = $page;
            if (is_string($this->page->count)) {
                $this->page->count = "true" === strtolower($this->page->count) || "1" === $this->page->count;
            } else if (is_int($this->page->count)) {
                $this->page->count = $this->page->count === 1;
            }
        }        
        return $this;
    }

    public function start() {
        if (func_num_args() === 0) {
            return $this->start;
        }
        $this->start = func_get_args()[0];
        return $this;
    }

    public function limit() {
        if (func_num_args() === 0) {
            return $this->limit;
        }
        $this->limit = func_get_args()[0];
        return $this;
    }

    public function filter() {
        if (func_num_args() === 0) {
            return $this->filter;
        }
        $filter = func_get_args()[0];
        if (is_string($filter)) {
            $arr = explode("|", $filter);
            $filter = [];
            foreach ($arr as $str) {
                $item = explode("=", $str);
                $filter[$item[0]] = count($item) > 1 ? $item[1] : null;
            }            
        }
        if (is_array($filter)) {
            if (array_keys($filter) !== range(0, count($filter) - 1)) {
                foreach($filter as $key => $value) {
                    $arr  = explode(self::$DELIMITER, trim($key));
                    $fit  = strtolower($arr[0]);
                    $item = [];
                    $item['and'] = true;
                    if ($fit === "and" || $fit === "or") {
                        $item['and'] = $fit === "and";
                        array_splice($arr, 0, 1);
                    }
                    $length = count($arr);
                    $lit = strtolower($arr[$length - 1]);
                    $item['rule'] = self::EQUAL;
                    if (in_array($lit, self::$RULES)) {
                        $item['rule'] = $lit;
                        array_splice($arr, $length - 1, 1);
                    }
                    $item['name'] = implode(self::$DELIMITER, $arr);
                    $item['value'] = $value;
                    array_push($this->filter, $item);
                }
            } else {
                $this->filter = $filter;
            }
        }
        return $this;
    }

    public function order() {
        if (func_num_args() === 0) {
            return $this->order;
        }
        $order = func_get_args()[0];
        if (is_string($order)) {
            $order = array_map("trim", explode(",", $order));
        }
        if (is_array($order) && array_keys($order) === range(0, count($order) - 1)) {
            $this->order = $order;
        }
        return $this;
    }

    public function group() {
        if (func_num_args() === 0) {
            return $this->group;
        }
        $group = func_get_args()[0];
        if (is_string($group)) {
            $group = array_map("trim", explode(",", $group));
        }
        if (is_array($group) && array_keys($group) === range(0, count($group) - 1)) {
            $this->group = $group;
        }
        return $this;
    }

    public function field() {
        if (func_num_args() === 0) {
            return $this->field;
        }
        $field = func_get_args()[0];
        if (is_string($field)) {
            $field = array_map("trim", explode(",", $field));
        }
        if (is_array($field) && array_keys($field) === range(0, count($field) - 1)) {
            $this->field = $field;
        }
        return $this;
    }

    public function value() {
        if (func_num_args() === 0) {
            return $this->value;
        }
        $value = func_get_args()[0];
        if (is_string($value)) {
            $arr = explode("|", $value);
            $value = [];
            foreach ($arr as $str) {
                $item = explode("=", $str);
                $value[$item[0]] = count($item) > 1 ? $item[1] : null;
            }            
        }
        if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
            $this->value = $value;
        }
        return $this;
    }

    public function delimiter() {
        return self::$DELIMITER;
    }


    /**
     * RESET
     */
    public function reset() {
        $this->pk_value  = null;
        $this->page      = null;
        $this->start     = null;
        $this->limit     = null;
        $this->filter    = [];
        $this->order     = null;
        $this->group     = null;
        $this->field     = null;
        return $this;
    }



    /**
     * GET
     */
    public function get() {
        $dao = null;
        if (isset($this->pk_value)) {
            if (!isset($this->field)) {
                return $this->manager->getRepository($this->class)->find($this->pk_value);  
            } else {
                $dao = (new self($this->manager, $this->class, $this->logger))
                ->field($this->field)
                ->filter([$this->pk_field . "__" . self::EQUAL => $this->pk_value])
                ->limit(2);
            }
        } else if (isset($this->filter)) {
            $dao = (new self($this->manager, $this->class, $this->logger))
            ->field($this->field)
            ->filter($this->filter)
            ->limit(2);
        } else {
            throw new Exception("Invalid operation: pk or filter are required");
        }
        $result = $dao->select();
        $count = count($result);
        if ($count === 0) {
            return null;
        }
        if ($count !== 1) {
            throw new Exception("Return multiple object");
        }
        return $result[0];
    }



    /**
     * MAP
     */
    public function map($fieldname = null) {
        $map_key = $this->pk_field;
        if (isset($fieldname)) {
            $meta = $this->manager->getClassMetadata($this->class);
            if (!isset($meta->fieldMappings[$fieldname])) {
                throw new Exception("Unknown field: " . $fieldname);
            }
            $fmap = $meta->fieldMappings[$fieldname];
            if ((isset($fmap['id']) && $fmap['id'] === true) ||
                (isset($fmap['unique']) && $fmap['unique'] === true))
            {
                $map_key = $fieldname;            
            } else {
                throw new Exception("Not unique field: " . $fieldname);
            }
        }
        if (isset($this->field) && !in_array($map_key, $this->field)) {
            throw new Exception("Undefined in field(), for: " . $fieldname);
        }
        $dao = null;
        if (isset($this->pk_value)) {
            $dao = (new self($this->manager, $this->class, $this->logger))
            ->filter([$this->pk_field . "__" . self::IN => $this->pk_value]);            
        } else if (isset($this->filter)) {
            $dao = (new self($this->manager, $this->class, $this->logger))
            ->filter($this->filter);
        } else {
            throw new Exception("Invalid operation: pk or filter are required");
        }
        $data = $dao
        ->field($this->field)
        ->order($this->order)
        ->limit($this->limit)
        ->start($this->start)
        ->select();
        $prop_key = $this->ref_class->getProperty($map_key);
        $result = [];
        foreach ($data as $item) {
            if (isset($this->field)) {
                $result[$item[$map_key]] = $item;
            } else {
                $result[$prop_key->getValue($item)] = $item;
            }
        }
        return $result;
    }


    /**
     * COUNT
     */
    public function count() {
        $builder = $this->builder();
        $alias   = $builder->alias;
        $builder = $builder->builder;
        $builder->select("COUNT(" . $alias . ")");
        $query = $builder->getQuery();
        $this->log($query);
        $result = $query->getSingleScalarResult();
        return $result;
    }
    

    /**
     * SELECT
     */
    public function select() {
        $builder = $this->builder();
        $alias   = $builder->alias;
        $qjoin   = $builder->join;
        $qfield  = $builder->field;
        $builder = $builder->builder;

        /*
         * PAGE
         */
        $page = null;
        if (isset($this->page)) {
            $page = new Page($this->page->index, $this->page->size, $this->page->count);
            if ($page->count === true) {
                $pbuilder = clone($builder);
                $pbuilder->select("COUNT(" . $alias . ")");
                $query = $pbuilder->getQuery();
                $this->log($query);
                $records = $query->getSingleScalarResult();
                $page->setRecords($records);
                if ($records === 0) {
                    return $page;
                }
            }
        }

        /*
         * ORDER
         */
        if (isset($this->order)) {
            foreach ($this->order as $name) {
                $is_asc = true;
                if (substr($name, 0, 1) === "-") {
                    $is_asc = false;
                    $name = substr($name, 1);
                }
                $expname = explode(self::$DELIMITER, $name);
                $lenname = count($expname);
                $s_alias = $alias;
                $s_name  = $expname[$lenname - 1];
                if ($lenname > 1) {
                    $prevname = implode(self::$DELIMITER, array_slice($expname, 0, $lenname - 1));
                    $s_alias = $qjoin[$prevname][0];
                }
                $builder->addOrderBy($s_alias . "." . $name, $is_asc ? "ASC" : "DESC");
            }
        }

        /*
         * RESULT
         */
        if (isset($page)) {
            $builder->setFirstResult(($page->index - 1) * $page->size)->setMaxResults($page->size);            
        } else {
            if (isset($this->start)) {
                $builder->setFirstResult($this->start);
            }
            if (isset($this->limit)) {
                $builder->setMaxResults($this->limit);
            }            
        }
        if (isset($qfield)) {
            $builder->select($qfield);
        }
        $query = $builder->getQuery();
        $this->log($query);
        $result = $query->getResult();
        if (isset($page)) {
            $page->data = $result;
            return $page;
        } else {
            return $result;
        }

    }

    /**
     * UPDATE
     */
    public function update() {
        if (!isset($this->value)) {
            throw new Exception("Invalid operation: value is required");
        }

        if (isset($this->pk_value)) {
            $pk_value = $this->pk_value;
            if (!is_array($pk_value)) {
                $pk_value = [$pk_value];
            }
            $dql = "UPDATE ". $this->class ." o SET ";
            foreach ($this->value as $key => $value) {
                $dql .= "o." . $key . "=:" . $key . ",";
            }
            $dql = substr($dql, 0, -1) . " WHERE o.". $this->pk_field . " IN (:pk_value)";
            $query = $this->manager->createQuery($dql);
            foreach ($this->value as $key => $value) {
                $query->setParameter($key, $value);
            }
            $query->setParameter("pk_value", $pk_value);
            $this->log($query);
            $result = $query->execute();
            return $result;
        }

        if (isset($this->value) && count($this->filter) !== 0) {
            $where = $this->where(false);
            $wql   = $where[0];
            $param = $where[1];
            $alias = $where[2];
            $dql = "UPDATE ". $this->class ." ". $alias . " SET ";
            foreach ($this->value as $key => $value) {
                $dql .= $alias . "." . $key . "=:" . $key . ",";
            }
            $dql = substr($dql, 0, -1) . " WHERE " . $wql;
            $query = $this->manager->createQuery($dql);
            foreach ($this->value as $key => $value) {
                $query->setParameter($key, $value);
            }
            foreach ($param as $key => $value) {
                $query->setParameter($key, $value);
            }
            $this->log($query);
            $result = $query->execute();
            return $result;
        }

        throw new Exception("Invalid operation: pk or filter are required");
    }


    /**
     * SAVE
     */
    public function save($entity = null) {
        $pk_value = $this->pk_value;
        if (isset($entity)) {
            if (get_class($entity) !== $this->class) {
                throw new Exception("00-Invalid entity class");
            }
            $pk_value = $this->ref_class->getProperty($this->pk_field)->getValue($entity);
        }
        $is_new = !isset($pk_value);
        if ($is_new) {
            if (!isset($entity)) {
                if (!isset($this->value)) {
                    throw new Exception("01-Invalid operation: entity or value are required");
                }
                $entity = $this->ref_class->newInstance();
                foreach ($this->value as $key => $value) {
                    if (!$this->ref_class->hasProperty($key)) continue;
                    $property = $this->ref_class->getProperty($key);
                    if (!$property->isPublic()) continue;
                    $property->setValue($entity, $value);
                }
            }
            if ($this->has_time) {
                if (!isset($entity->createdAt)) {
                    $entity->createdAt = new \DateTime();
                }
                if (!isset($entity->updatedAt)) {
                    $entity->updatedAt = new \DateTime();
                }
            }    
        } else {
            if (!isset($entity)) {
                if (!isset($this->value)) {
                    throw new Exception("01-Invalid operation: entity or value are required");
                }
                $entity = $this->manager->getRepository($this->class)->find($pk_value);
                if (!isset($entity)) {
                    throw new Exception("02-Entity is not found");
                }
                foreach ($this->value as $key => $value) {
                    if (!$this->ref_class->hasProperty($key)) continue;
                    $property = $this->ref_class->getProperty($key);
                    if (!$property->isPublic()) continue;
                    $property->setValue($entity, $value);
                }   
            }
            if ($this->has_time) {
                $entity->updatedAt = new \DateTime();
            }
        }
        $this->manager->persist($entity);
        $this->manager->flush();
        return $entity;        
    }


    /**
     * DELETE
     */
    public function delete($entity = null) {
        
        if (isset($entity) && get_class($entity) === $this->class) {
            if ($this->soft_delete === true) {
                $entity->setDeleted(true);
                $this->manager->persist($entity);
            } else {
                $this->manager->remove($entity);
            }
            $this->manager->flush();
            return $entity;            
        }

        if (isset($this->pk_value)) {
            $pk_value = $this->pk_value;
            if (!is_array($pk_value)) {
                $pk_value = [$pk_value];
            }
            $query = null;
            if ($this->soft_delete === true) {
                $query = $this->manager->createQuery(
                    "UPDATE ". $this->class ." o SET o.deleted=:deleted WHERE o." . $this->pk_field . " IN (:pk_value)"
                );
                $query->setParameter("deleted", true);
            } else {
                $query = $this->manager->createQuery(
                    "DELETE FROM ". $this->class ." o WHERE o." . $this->pk_field . " IN (:pk_value)"
                );
            }
            $query->setParameter("pk_value", $pk_value);
            $this->log($query);
            $result = $query->execute();
            return $result;
        } 

        if (count($this->filter) !== 0) {
            $where = $this->where(false);
            $dql   = $where[0];
            $param = $where[1];
            $alias = $where[2];
            $query = null;
            if ($this->soft_delete === true) {
                $query = $this->manager->createQuery(
                    "UPDATE ". $this->class ." " . $alias . " SET " . $alias . ".deleted=:deleted WHERE " . $dql
                );
                $query->setParameter("deleted", true);
            } else {
                $query = $this->manager->createQuery(
                    "DELETE FROM ". $this->class ." " . $alias . " WHERE " . $dql
                );
            }
            foreach ($param as $key => $value) {
                $query->setParameter($key, $value);
            }
            $this->log($query);
            $result = $query->execute();
            return $result;
        }

        throw new Exception("Invalid operation: entity or pk or filter are required");
    }


    /**
     * REFRESH
     */
    public function refresh($entity) {
        if (isset($entity) && get_class($entity) === $this->class) {
            $this->manager->refresh($entity);
        }
        return $entity;
    }




    private function where($select = true) {
        $query  = "";
        $param  = [];
        $join   = [];
        $count  = 0;
        $alias  = "a" . $count;
        if (count($this->filter) === 0) {
            return [$query, $param, $alias, $join]; // [query, param, alias, join] => join = ["a0.user"=>"a1", "a1.role"=>"a2"]
        }
        $query .= "1=1";
        if ($this->soft_delete === true) {
            $query .= " AND " . $alias . ".deleted=:" . $alias . "_deleted";
            $param[$alias . "_deleted"] = false;
        }
        foreach ($this->filter as $item) {
            $i_dql = $item["and"] === true ? " AND " : " OR ";
            $value = $item["value"];
            $name  = $item["name"];
            $rule  = $item["rule"];

            $expname = explode(self::$DELIMITER, $name);
            $lenname = count($expname);
            $i_alias = $alias;
            $i_name  = $expname[$lenname - 1];
            if ($select === true && $lenname > 1) {                
                $count++;
                $join[$expname[0]] = ["a" . $count, $alias . "." . $expname[0]];
                for ($i = 1; $i < $lenname - 1; $i++) {
                    $altname = implode(self::$DELIMITER, array_slice($expname, 0, $i + 1));
                    if (!isset($join[$altname])) {
                        $count++;
                        $prevname = implode(self::$DELIMITER, array_slice($expname, 0, $i));
                        $join[$altname] = ["a" . $count, $join[$prevname][0] . "." . $expname[$i]];
                    }
                }
                $prevname = implode(self::$DELIMITER, array_slice($expname, 0, $lenname - 1));
                $i_alias = $join[$prevname][0];
                $i_dql .= $i_alias . "." . $i_name;
            } else {
                $i_name = $name;
                $i_dql .= $i_alias . "." . $i_name;
            }
            switch ($rule) {
                case self::LIKE:
                    if (is_string($value) && trim($value) !== "") {
                        $query .= $i_dql . " LIKE :" . $i_alias . "_" . $i_name; 
                        $param[$i_alias . "_" . $i_name] = "%" . $value . "%";
                    } else {
                        throw new Exception("Invalid '" . $name . "__" . $rule . "' value");
                    }
                    break;
                case self::START:
                    if (is_string($value) && trim($value) !== "") {
                        $query .= $i_dql . " LIKE :" . $i_alias . "_" . $i_name; 
                        $param[$i_alias . "_" . $i_name] = $value . "%";
                    } else {
                        throw new Exception("Invalid '" . $name . "__" . $rule . "' value");
                    }
                    break;
                case self::END:
                    if (is_string($value) && trim($value) !== "") {
                        $query .= $i_dql . " LIKE :" . $i_alias . "_" . $i_name; 
                        $param[$i_alias . "_" . $i_name] = "%" . $value;
                    } else {
                        throw new Exception("Invalid '" . $name . "__" . $rule . "' value");
                    }
                    break;
                case self::BETWEEN:
                    if (is_string($value)) {
                        $arr = array_map("trim", explode(",", $value));
                        $start = $arr[0];
                        $end = count($arr) > 1 ? $arr[1] : null;
                    } else if (is_array($value)) {
                        $start = $value[0];
                        $end = count($value) > 1 ? $value[1] : null;
                    }
                    if (isset($start) && isset($end)) {
                        $query .= $i_dql . " BETWEEN :" . $i_alias . "_" . $i_name . "_s AND " . $i_alias . "_" . $i_name . "_e";
                        $param[$i_alias . "_" . $i_name . "_s"] = $start;
                        $param[$i_alias . "_" . $i_name . "_e"] = $end;
                    } else {
                        throw new Exception("Invalid '" . $name . "__" . $rule . "' value");
                    }
                    break;
                case self::NOT_NULL:
                    $query .= $i_dql . " IS NOT NULL";
                    break;
                case self::IS_NULL:
                    $query .= $i_dql . " IS NULL";
                    break;
                case self::GREATER_THAN:
                    $query .= $i_dql . " > :" . $i_alias . "_" . $i_name;
                    $param[$i_alias . "_" . $i_name] = $value;
                    break;
                case self::GREATER_EQUAL:
                    $query .= $i_dql . " >= :" . $i_alias . "_" . $i_name;
                    $param[$i_alias . "_" . $i_name] = $value;
                    break;
                case self::LESS_THAN:
                    $query .= $i_dql . " < :" . $i_alias . "_" . $i_name;
                    $param[$i_alias . "_" . $i_name] = $value;
                    break;
                case self::LESS_EQUAL:
                    $query .= $i_dql . " <= :" . $i_alias . "_" . $i_name;
                    $param[$i_alias . "_" . $i_name] = $value;
                    break;
                case self::IN:
                    $arrval = null;
                    if (is_string($value)) {
                        $arrval = array_map("trim", explode(",", $value));
                    } else if (is_array($value) && array_keys($value) === range(0, count($value) - 1)) {
                        $arrval = $value;
                    } else {
                        throw new Exception("Invalid '" . $name . "__" . $rule . "' value");                        
                    }
                    $query .= $i_dql . " IN (:" . $i_alias . "_" . $i_name . ")";
                    $param[$i_alias . "_" . $i_name] = $value;
                    break;
                case self::EQUAL:
                    
                default:
                    $query .= $i_dql . " = :" . $i_alias . "_" . $i_name;
                    $param[$i_alias . "_" . $i_name] = $value;
                    break;
            }

        }
        return [$query, $param, $alias, $join];
    }

    private function builder() {
        $where  = $this->where();
        $wheql  = $where[0];
        $wparam = $where[1];
        $alias  = $where[2];
        $wjoin  = $where[3];

        $builder = $this->manager->getRepository($this->class)->createQueryBuilder($alias);

        /*
         * FIELD
         */
        $qfield = null;
        $qjoin = [];
        if (isset($this->field) && count($this->field) > 0) {
            $count = 0;
            $s_dql = "";
            foreach ($this->field as $name) {
                $expname = explode(self::$DELIMITER, $name);
                $lenname = count($expname);
                $s_alias = $alias;
                $s_name  = $expname[$lenname - 1];
                if ($lenname > 1) {
                    for ($i = 1; $i < $lenname - 1; $i++) {
                        $altname = implode(self::$DELIMITER, array_slice($expname, 0, $i + 1));
                        if (!isset($qjoin[$altname])) {
                            if (isset($wjoin[$altname])) {
                                $qjoin[$altname] = $wjoin[$altname];
                                unset($wjoin[$altname]);
                            } else {
                                $count++;
                                $prevname = implode(self::$DELIMITER, array_slice($expname, 0, $i));
                                $qjoin[$altname] = ["b" . $count, $join[$prevname][0] . "." . $expname[$i]];
                            }
                        }
                        $prevname = implode(self::$DELIMITER, array_slice($expname, 0, $lenname - 1));
                        $s_alias = $qjoin[$prevname][0];
                        $s_dql .= $s_alias . "." . $s_name . ","; 
                    }
                } else {
                    $s_name = $name;
                    $s_dql .= $s_alias . "." . $s_name . ",";
                }
            }
            $s_dql = substr($s_dql, 0, -1);
            $qfield = $s_dql;            
        }
        foreach ($wjoin as $key => $value) {
            $qjoin[$key] = $value; 
        }
        unset($wjoin);

        /*
         * WHERE
         */
        if ($wheql !== "") {
            $builder->where($wheql);
            foreach ($wparam as $key => $value) {
                $builder->setParameter($key, $value);
            }
        }

        /*
         * GROUP
         */
        if (isset($this->group)) {
            foreach ($this->group as $name) {
                $expname = explode(self::$DELIMITER, $name);
                $lenname = count($expname);
                $s_alias = $alias;
                $s_name  = $expname[$lenname - 1];
                if ($lenname > 1) {
                    $prevname = implode(self::$DELIMITER, array_slice($expname, 0, $lenname - 1));
                    $s_alias = $qjoin[$prevname][0];
                }
                $builder->addGroupBy($s_alias . "." . $name);
            }
        }

        /*
         * JOIN
         */
        foreach($qjoin as $key => $value) {
            $builder->join($value[1], $value[0]);
        }
        
        return (object)array(
            "builder" => $builder, 
            "alias" => $alias, 
            "join" => $qjoin,
            "field" => $qfield
        );
    }

    private function log($query) {
        if (self::$DEBUG && isset($this->logger)) {
            $this->logger->debug("SQL=[" . $query->getSql() . "]");
            $params = "";
            foreach ($query->getParameters()->toArray() as $item) {
                $params .= $item->getName() . "=";
                if (is_array($item->getValue())) {
                    $params .= implode(",", $item->getValue());
                } else {
                    $params .= $item->getValue();
                }
                $params .= "|";
            }
            $params = substr($params, 0, -1);
            $this->logger->debug("PARAMS=[" . $params . "]");
        }
    }

}