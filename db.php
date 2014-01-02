<?php

class ActiveRecord {

    private static $get_instance;
    private static $connection;
    public $table;
    private $query;
    private $sql;
    private $columns;
    private $where;
    private static $types = array(
        'VARCHAR(255)',
        'INT(11)',
        'DOUBLE',
    );

    private function __construct() {
        ;
    }

    public static function initiate($dsn, $username, $password) {
        try {
            self::$connection = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            echo 'there is a problem : ' . $e->getMessage();
        }
        return self::create_instance();
    }

    private static function create_instance() {
        if (!self::$get_instance instanceof ActiveRecord) {
            self::$get_instance = new self;
        }
        return self::$get_instance;
    }

    public function __set($name, $value) {
        $this->columns[$name] = $value;
    }

    public function save($table) {
        $this->table = $table;
        $list_columns = $this->list_columns();
        foreach ($this->columns as $key => $value) {
            if (!array_key_exists($key, array_flip($list_columns))) {
                $add_columns[$key] = $value;
            }
        }
        if (isset($add_columns) and !is_null($add_columns)) {
            $this->add_columns($add_columns);
        }
        if (is_null($this->where)) {
            $this->insert($this->columns);
        } else {
            $this->update($this->columns);
        }
    }

    private function list_columns() {
        $this->query = self::$connection->prepare('SHOW COLUMNS FROM ' . $this->table);
        $this->execute();
        return $this->query->fetchAll(PDO::FETCH_COLUMN);
    }

    private function add_columns($attributes) {
        $this->sql = 'ALTER TABLE ' . $this->table . ' ADD %s %s' . ' NULL DEFAULT NULL';
        foreach ($attributes as $key => $value) {
            switch (gettype($value)) {
                case 'string':
                    $this->sql = sprintf($this->sql, $key, self::$types[0]);
                    break;
                case 'int':
                    $this->sql = sprintf($this->sql, $key, self::$types[1]);
                    break;
                case 'double':
                    $this->sql = sprintf($this->sql, $key, self::$types[2]);
                default:
                    throw new Exception('the types of data must be string,int or double ! ');
                    break;
            }
            $this->query = self::$connection->prepare($this->sql);
            return $this->execute();
        }
    }

    private function insert($attributes) {
        $this->sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, rtrim(implode(',', array_keys($attributes)), ','), rtrim(str_repeat('?,', count($attributes)), ','));
        $this->query = self::$connection->prepare($this->sql);
        return $this->execute(array_values($attributes));
    }

    private function execute($attributes = null) {
        if (is_object($this->query)) {
            if (!is_null($attributes)) {
                return $this->query->execute($attributes);
            }
            return $this->query->execute();
        }
        return false;
    }

    private function update() {
        
    }

}

$Ar = ActiveRecord::initiate('mysql:dbhost=localhost;dbname=laravel', 'root', '');
$Ar->username = "saeed";
$Ar->password = "123456789";
$Ar->created_date = date('Y M i');
$Ar->session = "session";
$Ar->save('users');