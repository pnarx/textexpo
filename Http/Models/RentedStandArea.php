<?php 
namespace Http\Models;

use Http\Models\Model;

class RentedStandArea extends Model {
    private $table = "rented_stand_area";
    
    public function updateOrCreate($dto) {
        $sql = "SELECT * FROM " . $this->table . " WHERE pre_application_id = :preApplicationId LIMIT 1";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':preApplicationId', $dto['pre_application_id'], \PDO::PARAM_INT);
        $qry->execute();
        
        $rentedStandArea = $qry->fetch(\PDO::FETCH_ASSOC);
        
        if (!$rentedStandArea) {
            $insertSQL = "INSERT INTO " .$this->table. " (
                pre_application_id,
                stant_salon_numarasi,
                ozel_dekarasyon_brim,
                ozel_dekarasyon_fiyat,
                ozel_dekarasyon_toplam_fiyat,
                sponsorluk_brim,
                sponsorluk_fiyat,
                sponsorluk_toplam_fiyat,
                meydana_bakan_cephe_farki_brim,
                meydana_bakan_cephe_farki_fiyat,
                meydana_bakan_cephe_farki_toplam_fiyat,
                cift_kat_stand_farki_brim,
                cift_kat_stand_farki_fiyat,
                cift_kat_stand_farki_toplam_fiyat,
                dis_alan_brim,
                dis_alan_fiyat,
                dis_alan_toplam_fiyat,
                status
            ) VALUES (
                :pre_application_id,
                :stant_salon_numarasi,
                :ozel_dekarasyon_brim,
                :ozel_dekarasyon_fiyat,
                :ozel_dekarasyon_toplam_fiyat,
                :sponsorluk_brim,
                :sponsorluk_fiyat,
                :sponsorluk_toplam_fiyat,
                :meydana_bakan_cephe_farki_brim,
                :meydana_bakan_cephe_farki_fiyat,
                :meydana_bakan_cephe_farki_toplam_fiyat,
                :cift_kat_stand_farki_brim,
                :cift_kat_stand_farki_fiyat,
                :cift_kat_stand_farki_toplam_fiyat,
                :dis_alan_brim,
                :dis_alan_fiyat,
                :dis_alan_toplam_fiyat,
                :status
            )";
            
            $insertQRY = $this->db->prepare($insertSQL);
            
            $insertQRY->bindValue(':pre_application_id', $dto['pre_application_id'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':stant_salon_numarasi', $dto['stant_salon_numarasi'], \PDO::PARAM_STR);
            $insertQRY->bindValue(':ozel_dekarasyon_brim', $dto['ozel_dekarasyon_brim'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':ozel_dekarasyon_fiyat', $dto['ozel_dekarasyon_fiyat'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':ozel_dekarasyon_toplam_fiyat', $dto['ozel_dekarasyon_toplam_fiyat'], \PDO::PARAM_INT); // String for price
            $insertQRY->bindValue(':sponsorluk_brim', $dto['sponsorluk_brim'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':sponsorluk_fiyat', $dto['sponsorluk_fiyat'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':sponsorluk_toplam_fiyat', $dto['sponsorluk_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $insertQRY->bindValue(':meydana_bakan_cephe_farki_brim', $dto['meydana_bakan_cephe_farki_brim'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':meydana_bakan_cephe_farki_fiyat', $dto['meydana_bakan_cephe_farki_fiyat'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':meydana_bakan_cephe_farki_toplam_fiyat', $dto['meydana_bakan_cephe_farki_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $insertQRY->bindValue(':cift_kat_stand_farki_brim', $dto['cift_kat_stand_farki_brim'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':cift_kat_stand_farki_fiyat', $dto['cift_kat_stand_farki_fiyat'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':cift_kat_stand_farki_toplam_fiyat', $dto['cift_kat_stand_farki_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $insertQRY->bindValue(':dis_alan_brim', $dto['dis_alan_brim'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':dis_alan_fiyat', $dto['dis_alan_fiyat'], \PDO::PARAM_INT);
            $insertQRY->bindValue(':dis_alan_toplam_fiyat', $dto['dis_alan_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $insertQRY->bindValue(':status', 'active', \PDO::PARAM_STR);
            
            $insertQRY->execute();
            
            $lastInsertId = $this->db->lastInsertId();
            
            return [
                "status" => true,
                "data" => [
                    "rented_stand_area_id" => $lastInsertId
                ],
                "message" => 'İşlem Başarılı'
            ];
        }
        
        $updateSQL = "UPDATE " . $this->table . " SET
                stant_salon_numarasi = :stant_salon_numarasi,
                ozel_dekarasyon_brim = :ozel_dekarasyon_brim,
                ozel_dekarasyon_fiyat = :ozel_dekarasyon_fiyat,
                ozel_dekarasyon_toplam_fiyat = :ozel_dekarasyon_toplam_fiyat,
                sponsorluk_brim = :sponsorluk_brim,
                sponsorluk_fiyat = :sponsorluk_fiyat,
                sponsorluk_toplam_fiyat = :sponsorluk_toplam_fiyat,
                meydana_bakan_cephe_farki_brim = :meydana_bakan_cephe_farki_brim,
                meydana_bakan_cephe_farki_fiyat = :meydana_bakan_cephe_farki_fiyat,
                meydana_bakan_cephe_farki_toplam_fiyat = :meydana_bakan_cephe_farki_toplam_fiyat,
                cift_kat_stand_farki_brim = :cift_kat_stand_farki_brim,
                cift_kat_stand_farki_fiyat = :cift_kat_stand_farki_fiyat,
                cift_kat_stand_farki_toplam_fiyat = :cift_kat_stand_farki_toplam_fiyat,
                dis_alan_brim = :dis_alan_brim,
                dis_alan_fiyat = :dis_alan_fiyat,
                dis_alan_toplam_fiyat = :dis_alan_toplam_fiyat,
                status = :status
            WHERE pre_application_id = :pre_application_id"; // Corrected to include table name
            
            $updateQRY = $this->db->prepare($updateSQL);
            
            $updateQRY->bindValue(':pre_application_id', $dto['pre_application_id'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':stant_salon_numarasi', $dto['stant_salon_numarasi'], \PDO::PARAM_STR);
            $updateQRY->bindValue(':ozel_dekarasyon_brim', $dto['ozel_dekarasyon_brim'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':ozel_dekarasyon_fiyat', $dto['ozel_dekarasyon_fiyat'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':ozel_dekarasyon_toplam_fiyat', $dto['ozel_dekarasyon_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $updateQRY->bindValue(':sponsorluk_brim', $dto['sponsorluk_brim'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':sponsorluk_fiyat', $dto['sponsorluk_fiyat'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':sponsorluk_toplam_fiyat', $dto['sponsorluk_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $updateQRY->bindValue(':meydana_bakan_cephe_farki_brim', $dto['meydana_bakan_cephe_farki_brim'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':meydana_bakan_cephe_farki_fiyat', $dto['meydana_bakan_cephe_farki_fiyat'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':meydana_bakan_cephe_farki_toplam_fiyat', $dto['meydana_bakan_cephe_farki_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $updateQRY->bindValue(':cift_kat_stand_farki_brim', $dto['cift_kat_stand_farki_brim'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':cift_kat_stand_farki_fiyat', $dto['cift_kat_stand_farki_fiyat'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':cift_kat_stand_farki_toplam_fiyat', $dto['cift_kat_stand_farki_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $updateQRY->bindValue(':dis_alan_brim', $dto['dis_alan_brim'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':dis_alan_fiyat', $dto['dis_alan_fiyat'], \PDO::PARAM_INT);
            $updateQRY->bindValue(':dis_alan_toplam_fiyat', $dto['dis_alan_toplam_fiyat'], \PDO::PARAM_STR); // String for price
            $updateQRY->bindValue(':status', 'active', \PDO::PARAM_STR);
            
            $updateQRY->execute();
            
            return [
                "status" => true,
                "data" => [
                    "rented_stand_area_id" => $rentedStandArea['id']
                ],
                "message" => 'Güncelleme Başarılı'
            ];
    }
    
    public function getByApplicationId($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE pre_application_id = :id";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':id', $id, \PDO::PARAM_INT);
        $qry->execute();
        
        return $qry->fetch(\PDO::FETCH_ASSOC);
    }
}