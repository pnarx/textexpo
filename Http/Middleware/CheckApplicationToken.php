<?php 

namespace Http\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CheckApplicationToken {
    public function handle($token) {
        $Model = (new \Http\Models\Model);
        $sql = "SELECT * FROM pre_application_stand WHERE token = :token AND status = :status";
        $qry = $Model->db->prepare($sql);
        $qry->bindValue(':token', $token, \PDO::PARAM_STR);
        $qry->bindValue(':status', 'active', \PDO::PARAM_STR);
        $qry->execute();
        $data = $qry->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            return true;
        }

        return false;
    }
}