<?php 
namespace Http\Models;

use Http\Models\Model;

class RentedStandAreaDetail extends Model {
    private $table = "rented_stand_area_detail";
    
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
                    hizmet_bedelleri_icerigi,
                    m2_kare_basina_hizmet_bedeli,
                    m2_basina_kati_atik_bedeli,
                    bir_yillik_online_hizmet_bedeli,
                    kdv_tutari,
                    genel_toplam,
                    sozlesme_damga_vergisi,
                    status
                ) VALUES (
                    :rented_stand_area_id,
                    :hizmet_bedelleri_icerigi,
                    :m2_kare_basina_hizmet_bedeli,
                    :m2_basina_kati_atik_bedeli,
                    :bir_yillik_online_hizmet_bedeli,
                    :kdv_tutari,
                    :genel_toplam,
                    :sozlesme_damga_vergisi,
                    :status
                )";
            
            $insertQRY = $this->db->prepare($insertSQL);
    
            $insertQRY->bindValue(':rented_stand_area_id', $rentedStandAreaId, \PDO::PARAM_INT);
            $insertQRY->bindValue(':hizmet_bedelleri_icerigi', $dto['hizmet_bedelleri_icerigi'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':m2_kare_basina_hizmet_bedeli', $dto['m2_basina_hizmet_bedeli'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':m2_basina_kati_atik_bedeli', $dto['m2_basina_katik_atik_bedeli'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':bir_yillik_online_hizmet_bedeli', $dto['bir_yillik_online_hizmet_bedeli'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':kdv_tutari', $dto['kdv'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':genel_toplam', $dto['genel_toplam_tl'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':sozlesme_damga_vergisi', $dto['sozlesme_damga_vergisi'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':status', 'active', \PDO::PARAM_STR);
    
            $insertQRY->execute();
            
            // Son eklenen ID'yi döndür
            $lastInsertId = $this->db->lastInsertId();
            
            return [
                "status" => true,
                "data" => [
                    "another_table_id" => $lastInsertId
                ],
                "message" => 'İşlem Başarılı'
            ];
        } 
        
        $updateSQL = "UPDATE " . $this->table . " SET 
                hizmet_bedelleri_icerigi = :hizmet_bedelleri_icerigi,
                m2_kare_basina_hizmet_bedeli = :m2_kare_basina_hizmet_bedeli,
                m2_basina_kati_atik_bedeli = :m2_basina_kati_atik_bedeli,
                bir_yillik_online_hizmet_bedeli = :bir_yillik_online_hizmet_bedeli,
                kdv_tutari = :kdv_tutari,
                genel_toplam = :genel_toplam,
                sozlesme_damga_vergisi = :sozlesme_damga_vergisi,
                status = :status
            WHERE rented_stand_area_id = :rented_stand_area_id";
            
        $updateQRY = $this->db->prepare($updateSQL);

        $updateQRY->bindValue(':rented_stand_area_id', $rentedStandAreaId, \PDO::PARAM_INT);
        $updateQRY->bindValue(':hizmet_bedelleri_icerigi', $dto['hizmet_bedelleri_icerigi'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':m2_kare_basina_hizmet_bedeli', $dto['m2_basina_hizmet_bedeli'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':m2_basina_kati_atik_bedeli', $dto['m2_basina_katik_atik_bedeli'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':bir_yillik_online_hizmet_bedeli', $dto['bir_yillik_online_hizmet_bedeli'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':kdv_tutari', $dto['kdv'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':genel_toplam', $dto['genel_toplam_tl'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':sozlesme_damga_vergisi', $dto['sozlesme_damga_vergisi'], \PDO::PARAM_STR);
        $updateQRY->bindValue(':status', 'active', \PDO::PARAM_STR);

        $updateQRY->execute();

        return [
            "status" => true,
            "data" => [
                "another_table_id" => $rentedStandAreaId
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
}