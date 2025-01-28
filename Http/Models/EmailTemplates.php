<?php 

namespace Http\Models;

class EmailTemplates extends Model {
    protected $table = 'email_templates';
    
    public function getEmailTemplatesByActive() {
        $sql = "select * from " . $this->table . " WHERE status = :status";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':status', 'active');
        $qry->execute();
        
        return $qry->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function updateById($id, $template) {
        $sql = "select * from " . $this->table . ' WHERE id = :id';
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':id', $id);
        $qry->execute();
        $data = $qry->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            
            $updateSQL = "UPDATE " . $this->table . " SET template = :template  WHERE id = :id";
            $updateQRY = $this->db->prepare($updateSQL);
            $updateQRY->bindValue(':template', $template);
            $updateQRY->bindValue(':id', $id);
            $updateQRY->execute();
            
            return [
                'status' => true,
                'data' => [],
                'message' => 'Ekleme işlemi başarılı'
            ];
        }
        
        return [
            'status' => false,
            'data' => [],
            'message' => 'hata'
        ];
    }
    
    public function getTemplateByStepNumber($step) {
        $sql = "SELECT * from " . $this->table . " WHERE step = :step";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':step', $step);
        $qry->execute();
        
        return $qry->fetch(\PDO::FETCH_ASSOC);
    }
}