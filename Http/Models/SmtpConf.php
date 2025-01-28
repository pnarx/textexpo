<?php 

namespace Http\Models;

class SmtpConf extends Model {
    protected $table = 'smtp_config';
    
    public function getSmtpConfByActive() {
        $sql = "select * from " . $this->table . " WHERE status = :status";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':status', 'active');
        $qry->execute();
        
        return $qry->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function updateSmtpConf($data) {
        $sql = "UPDATE " . $this->table . " SET host = :host, password = :password, username = :username, port = :port WHERE status = :status";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':host', $data['host']);
        $qry->bindValue(':password', $data['password']);
        $qry->bindValue(':username', $data['username']);
        $qry->bindValue(':port', $data['port']);
        $qry->bindValue(':status', 'active');
        $qry->execute();
    }
}