<?php

class ActiveRecord {

    private static $get_instance;
    private static $connection;
    private static $is_connected;
    public $table;
    private $query;
    private $sql;
    private $columns;
    private $where;

    /* private static $types = array(
      'int', 'double', 'varchar', 'datetime', 'date', 'text', 'time'
      ); */

    private function __construct() {
        ;
    }

    public static function initiate($dsn, $username, $password, $new_connection = false) {
        if (self::$is_connected === false || $new_connection === true) {
            try {
                self::$connection = new PDO($dsn, $username, $password);
                self::$is_connected = true;
            } catch (PDOException $e) {
                echo 'there is a problem : ' . $e->getMessage();
            }
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

        if (!is_null($add_columns)) {
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
        $this->query->execute();
        return $this->query->fetchAll(PDO::FETCH_COLUMN);
    }

    private function insert($attributes) {
        $this->sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, rtrim(implode(',', array_keys($attributes)), ','), rtrim(str_repeat('?,', count($attributes)), ','));
        $this->query = self::$connection->prepare($this->sql);
        return $this->query->execute(array_values($attributes));
    }

}