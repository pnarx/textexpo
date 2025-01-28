<?php 
namespace Http\Models;

use Http\Models\Model;

class RentedStandAreaPaymentPlan extends Model {
    private $table = "rented_stand_area_payment_plan";
    
    public function updateOrCreate($dto, $rentedStandAreaId) {

        $sql = "SELECT * FROM " . $this->table . " WHERE rented_stand_area_id = :rentedStandAreaId LIMIT 1";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':rentedStandAreaId', $rentedStandAreaId, \PDO::PARAM_INT);
        $qry->execute();
        
        $existingRecord = $qry->fetch(\PDO::FETCH_ASSOC);
    
        if (!$existingRecord) {
            // Eğer veri yoksa, insert işlemi yap
            $insertSQL = "INSERT INTO " . $this->table . " (
                    rented_stand_area_id,
                    pesinat,
                    bir_taksit,
                    iki_taksit,
                    uc_taksit,
                    dort_taksit,
                    status,
                    hepsi_odendi
                ) VALUES (
                    :rented_stand_area_id,
                    :pesinat,
                    :bir_taksit,
                    :iki_taksit,
                    :uc_taksit,
                    :dort_taksit,
                    :status,
                    :hepsi_odendi
                )";
            
            $insertQRY = $this->db->prepare($insertSQL);
    
            $insertQRY->bindValue(':rented_stand_area_id', $rentedStandAreaId, \PDO::PARAM_INT);
            $insertQRY->bindValue(':pesinat', $dto['pesinat'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':bir_taksit', $dto['bir_taksit'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':iki_taksit', $dto['iki_taksit'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':uc_taksit', $dto['uc_taksit'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':dort_taksit', $dto['dort_taksit'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':status', 'active', \PDO::PARAM_STR);
            $insertQRY->bindValue(':hepsi_odendi', $dto['hepsiOdendi'], \PDO::PARAM_INT);
    
            $insertQRY->execute();
        
            $lastInsertId = $this->db->lastInsertId();
            
            return [
                "status" => true,
                "data" => [
                    "second_table_id" => $lastInsertId
                ],
                "message" => 'İşlem Başarılı'
            ];
        }
        

        
        $updateSQL = "UPDATE " . $this->table . " SET 
                pesinat = :pesinat,
                bir_taksit = :bir_taksit,
                iki_taksit = :iki_taksit,
                uc_taksit = :uc_taksit,
                dort_taksit = :dort_taksit,
                status = :status,
                hepsi_odendi = :hepsi_odendi
            WHERE rented_stand_area_id = :rented_stand_area_id";

        $updateQRY = $this->db->prepare($updateSQL);
        
        $birTaksit = $dto['hepsiOdendi'] ? null : $dto['bir_taksit'];
        $ikiTaksit = $dto['hepsiOdendi'] ? null : $dto['iki_taksit'];
        $ucTaksit = $dto['hepsiOdendi'] ? null : $dto['uc_taksit'];
        $dortTaksit = $dto['hepsiOdendi'] ? null : $dto['dort_taksit'];
        $pesinat = $dto['hepsiOdendi'] ? $dto['genel_toplam_tl'] : $dto['pesinat'];

        $updateQRY->bindValue(':rented_stand_area_id', $rentedStandAreaId, \PDO::PARAM_INT);
        $updateQRY->bindValue(':pesinat', $pesinat, \PDO::PARAM_STR);
        $updateQRY->bindValue(':bir_taksit', $birTaksit, \PDO::PARAM_STR);
        $updateQRY->bindValue(':iki_taksit', $ikiTaksit, \PDO::PARAM_STR);
        $updateQRY->bindValue(':uc_taksit', $ucTaksit, \PDO::PARAM_STR);
        $updateQRY->bindValue(':dort_taksit', $dortTaksit, \PDO::PARAM_STR);
        $updateQRY->bindValue(':status', 'active', \PDO::PARAM_STR);
        $updateQRY->bindValue(':hepsi_odendi', $dto['hepsiOdendi'], \PDO::PARAM_INT);

        $updateQRY->execute();

        return [
            "status" => true,
            "data" => [
                "second_table_id" => $rentedStandAreaId
            ],
            "message" => 'Güncelleme Başarılı'
        ];
        
    }
    
    public function getByApplicationId($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE rented_stand_area_id = :id";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':id', $id, \PDO::PARAM_INT);
        $qry->execute();
        
        return $qry->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getTaksit() {
        $sql = "SELECT 
                payment_plan.rented_stand_area_id, 
                payment_plan.bir_taksit,
                payment_plan.pesinat,
                payment_plan.iki_taksit, 
                payment_plan.uc_taksit, 
                payment_plan.dort_taksit, 
                payment_plan.created_at, 
                payment_plan.status AS paymentPlanStatus, 
                payment_plan.hepsi_odendi,
                pre_application.company_name,
                pre_application.company_phone,
                pre_application.mobile_phone,
                pre_application.created_at as application_start_date,
                stand_area_detail.genel_toplam
            FROM rented_stand_area_payment_plan AS payment_plan
            INNER JOIN rented_stand_area AS stand_area ON payment_plan.rented_stand_area_id = stand_area.id
            INNER JOIN pre_application_stand AS pre_application ON stand_area.pre_application_id = pre_application.id
            INNER JOIN rented_stand_area_detail AS stand_area_detail ON stand_area.pre_application_id = stand_area_detail.rented_stand_area_id
            WHERE payment_plan.status = :status
        ";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':status', 'active');
        $qry->execute();
        
        return $qry->fetchAll(\PDO::FETCH_ASSOC);
    }
}