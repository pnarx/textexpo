<?php 
namespace Http\Models;

use Http\Models\Model;

class User extends Model {
    private $table = "users";
    public function getUsers() {
        $sql = "SELECT * FROM users";
        $qry = $this->db->query($sql);
        $qry->execute();

        return $qry->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function attempt($email, $password) {
        $sql = "SELECT * FROM " . $this->table . " WHERE nickname = :nickname LIMIT 1";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':nickname', $email, \PDO::PARAM_STR);
        $qry->execute();
        $user = $qry->fetch();
        
        if ($user) {
            return true;
        }
        
        return false;
    }
    
    public function user($email) {
        $sql = "SELECT * FROM " . $this->table . " WHERE nickname = :nickname LIMIT 1";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':nickname', $email, \PDO::PARAM_STR);
        $qry->execute();
        
        return $qry->fetch();
    }
    
    public function getUserById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':id', $id);
        $qry->execute();
        
        return $qry->fetch(\PDO::FETCH_ASSOC);
    }
}