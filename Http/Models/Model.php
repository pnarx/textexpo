<?php 

namespace Http\Models;

class Model {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $dbname = 'pinartesttt_expo';
    public $db;
    public function __construct() {
        try {
            $pdo = new \PDO("mysql:host=".$this->host.";dbname=".$this->dbname.";charset=utf8", $this->user, $this->password);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->db = $pdo;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }
}