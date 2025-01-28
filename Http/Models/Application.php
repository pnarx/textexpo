<?php 

namespace Http\Models;

class Application extends Model {
    protected $table = 'pre_application_stand ';
    public function saveApplication($PreApplicationDTO) : void {
        try {
            $sql = "INSERT INTO ".$this->table." (full_name, company_name, duty, company_web_site, email, company_phone, mobile_phone, question, message, status, token) VALUES (:full_name, :company_name, :duty, :company_web_site, :email, :company_phone, :mobile_phone, :question, :message, :status, :token)";
            $qry = $this->db->prepare($sql);

            $length = 16;
            $randomString = substr(bin2hex(random_bytes($length)), 0, $length);

            $qry->bindValue(':full_name', $PreApplicationDTO['full_name']);
            $qry->bindValue(':company_name', $PreApplicationDTO['company_name']);
            $qry->bindValue(':duty', $PreApplicationDTO['duty']);
            $qry->bindValue(':company_web_site', $PreApplicationDTO['company_website']);
            $qry->bindValue(':email', $PreApplicationDTO['email']);
            $qry->bindValue(':company_phone', $PreApplicationDTO['company_phone']);
            $qry->bindValue(':mobile_phone', $PreApplicationDTO['mobile_phone']);
            $qry->bindValue(':question', $PreApplicationDTO['hear_about_us']);
            $qry->bindValue(':message', $PreApplicationDTO['message']);
            $qry->bindValue(':status', 'waiting');
            $qry->bindValue(':token', $randomString);

            $qry->execute();
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
        }
    }
    
    public function getPreApplicationById($id) {
        $sql = "SELECT 
            id, full_name, company_name, duty, company_web_site, email, company_phone, mobile_phone, question, message, created_at, token, status, response_user_id 
            FROM 
            ".$this->table." 
             WHERE id = :id";
         $qry = $this->db->prepare($sql);
         $qry->bindValue(':id', $id, \PDO::PARAM_INT);
         $qry->execute();
         
         return $qry->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function preApplicationByToken($token) {
        $sql = "SELECT 
            id, full_name, company_name, duty, company_web_site, email, company_phone, mobile_phone, question, message, created_at, token, status 
            FROM 
            ".$this->table." 
             WHERE token = :token";
         $qry = $this->db->prepare($sql);
         $qry->bindValue(':token', $token, \PDO::PARAM_STR);
         $qry->execute();
         
         return $qry->fetch(\PDO::FETCH_ASSOC);
    }

    public function getPreApplications() {
        $limit = 10;
        $sql = "SELECT 
            id, full_name, company_name, duty, company_web_site, email, company_phone, mobile_phone, question, message, created_at, token, status, response_user_id 
            FROM 
            ".$this->table." 
            ORDER BY created_at DESC";
        $qry = $this->db->prepare($sql);
        $qry->execute();
    
        $preApplications = $qry->fetchAll(\PDO::FETCH_OBJ);
        
        if (!$preApplications) {
            return [
                "status" => false,
                "data" => [],
                "message" => "Ön başvuru bulunamadı."
            ];
        }
    
        return [
            "status" => true,
            "data" => $preApplications,
            "message" => "Veriler listelendi"
        ];
    }
    
     public function getPreApplicationDetail($applicationId) {
        $limit = 10;
        $sql = "SELECT 
            id, full_name, company_name, duty, company_web_site, email, company_phone, mobile_phone, question, message, created_at, token, status FROM ".$this->table." WHERE id = :id LIMIT 1";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':id', $applicationId, \PDO::PARAM_INT);
        $qry->execute();
    
        $preApplications = $qry->fetch(\PDO::FETCH_ASSOC);
        
        if (!$preApplications) {
            return [
                "status" => false,
                "data" => [],
                "message" => "Ön başvuru bulunamadı."
            ];
        }
    
        return [
            "status" => true,
            "data" => $preApplications,
            "message" => "Veriler listelendi"
        ];
    }
    
    public function continuingApplicaiton() {
        $sql = "SELECT * from ". $this->table . " WHERE status = :status ORDER BY updated_at DESC";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':status', 'active', \PDO::PARAM_STR);
        $qry->execute();
        $continuingApplicaitons = $qry->fetchAll(\PDO::FETCH_ASSOC);
        
        if (!$continuingApplicaitons) {
            return [
                "status" => false,
                "data" => [],
                "message" => "Devam eden başvuru bulunamadı."
            ];
        }
    
        return [
            "status" => true,
            "data" => $continuingApplicaitons,
            "message" => "Veriler listelendi"
        ];
    }
    
    public function approvalPreApplication($applicationId) {
        $date = date('Y-m-d H:i:s');
        $sql = "UPDATE ".$this->table." SET status = :status, response_user_id = :response_user_id, updated_at = :updatedAt WHERE id = :id";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':status', 'active', \PDO::PARAM_STR);
        $qry->bindValue(':response_user_id', $_SESSION['user']['id'], \PDO::PARAM_STR);
        $qry->bindValue(':updatedAt', $date, \PDO::PARAM_STR);
        $qry->bindValue(':id', $applicationId, \PDO::PARAM_INT);
        $qry->execute();
    
        $selectSql = "SELECT * FROM pre_application_stand WHERE id = :id";
        $selectQry = $this->db->prepare($selectSql);
        $selectQry->bindValue(':id', $applicationId, \PDO::PARAM_INT);
        $selectQry->execute();
        $updatedData = $selectQry->fetch(\PDO::FETCH_ASSOC);
        
        return [
            "status" => true,
            "data" => [],
            "message" => "Ön başvuru onaylandı"
        ];
    }
    
    public function getPreApplicationWithDetail($token) {
        $collect = [];
        
        $preApplicationSQL = "SELECT * FROM " . $this->table . " WHERE token = :token LIMIT 1";
        $preApplicationQRY = $this->db->prepare($preApplicationSQL);
        $preApplicationQRY->bindValue(':token', $token);
        $preApplicationQRY->execute();
        $preApplicationData = $preApplicationQRY->fetch(\PDO::FETCH_ASSOC);
        
        $collect["pre_application"] = $preApplicationData;
        
        $applicationDetailSQL = "SELECT * FROM pre_application_detail_stand WHERE pre_application_id = :preApplicationId LIMIT 1";
        $applicationDetailQRY = $this->db->prepare($applicationDetailSQL);
        $applicationDetailQRY->bindValue(':preApplicationId', $preApplicationData['id']);
        $applicationDetailQRY->execute();
        $applicationDetailData = $applicationDetailQRY->fetch(\PDO::FETCH_ASSOC);
        
        $collect["pre_application_detail"] = !$applicationDetailData ? null : $applicationDetailData;
        
        return $collect;
    }
}