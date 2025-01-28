<?php 

namespace Http\Models;

class ApplicationDetail extends Model {
    protected $table = 'pre_application_detail_stand';
    
    public function saveDetailByApplicationId($applicationDetailDTO) {
            try {
                $sql = "INSERT INTO ". $this->table ." 
                    (pre_application_id, address, city, district, country, company_representative, company_official_title, tax_number, tax_office, fair_manager, fair_manager_mobile_phone, fair_manager_email, finance_officer_title, finance_officer_phone, finance_officer_email, nace_code, mersis_number, kep_address, mikro_obi, saha_istanbul_member_status, saha_expo_sales, company_email, company_manager_phone, company_manager_email, status, vakif_status) 
                    VALUES 
                    (:pre_application_id, :address, :city, :district, :country, :company_representative, :company_official_title, :tax_number, :tax_office, :fair_manager, :fair_manager_mobile_phone, :fairManagerEmail, :finance_officer_title, :finance_officer_phone, :finance_officer_email, :nace_code, :mersis_number, :kep_address, :mikro_obi, :saha_istanbul_member_status, :saha_expo_sales, :company_email, :company_manager_phone, :company_manager_email, :status, :vakif_status)";
                $qry = $this->db->prepare($sql);
                $qry->bindValue(':pre_application_id', $applicationDetailDTO['pre_application_id'], \PDO::PARAM_INT);
                $qry->bindValue(':address', $applicationDetailDTO['address'], \PDO::PARAM_STR);
                $qry->bindValue(':city', $applicationDetailDTO['city'], \PDO::PARAM_STR);
                $qry->bindValue(':district', $applicationDetailDTO['district'], \PDO::PARAM_STR);
                $qry->bindValue(':country', $applicationDetailDTO['country'], \PDO::PARAM_STR);
                $qry->bindValue(':company_representative', $applicationDetailDTO['companyRepresentative'], \PDO::PARAM_STR);
                $qry->bindValue(':company_official_title', $applicationDetailDTO['companyManager'], \PDO::PARAM_STR);
                $qry->bindValue(':tax_number', $applicationDetailDTO['taxNumber'], \PDO::PARAM_STR);
                $qry->bindValue(':tax_office', $applicationDetailDTO['taxOffice']);
                $qry->bindValue(':fair_manager', $applicationDetailDTO['expoManagerTitle'], \PDO::PARAM_STR);
                $qry->bindValue(':fair_manager_mobile_phone', $applicationDetailDTO['expoManagerPhone'], \PDO::PARAM_STR);
                $qry->bindValue(':fairManagerEmail', $applicationDetailDTO['expoManagerEmail'], \PDO::PARAM_STR);
                $qry->bindValue(':finance_officer_title', $applicationDetailDTO['financeManagerTitle'], \PDO::PARAM_STR);
                $qry->bindValue(':finance_officer_phone', $applicationDetailDTO['financeManagerPhone'], \PDO::PARAM_STR);
                $qry->bindValue(':finance_officer_email', $applicationDetailDTO['financeManagerEmail'], \PDO::PARAM_STR);
                $qry->bindValue(':nace_code', $applicationDetailDTO['naceNo'], \PDO::PARAM_STR);
                $qry->bindValue(':mersis_number', $applicationDetailDTO['mersisNo'], \PDO::PARAM_STR);
                $qry->bindValue(':kep_address', $applicationDetailDTO['kepAddress'], \PDO::PARAM_STR);
                $qry->bindValue(':mikro_obi', $applicationDetailDTO['companyStatus'], \PDO::PARAM_STR);
                $qry->bindValue(':saha_istanbul_member_status', $applicationDetailDTO['memberStatus'], \PDO::PARAM_STR);
                $qry->bindValue(':saha_expo_sales', $applicationDetailDTO['expoSales'], \PDO::PARAM_STR);
                $qry->bindValue(':company_email', $applicationDetailDTO['companyEmail'], \PDO::PARAM_STR);
                $qry->bindValue(':company_manager_phone', $applicationDetailDTO['companyManagerPhone'], \PDO::PARAM_STR);
                $qry->bindValue(':company_manager_email', $applicationDetailDTO['responsibleEmail'], \PDO::PARAM_STR);
                $qry->bindValue(':status', 'done', \PDO::PARAM_STR);
                $qry->bindValue(':vakif_status', $applicationDetailDTO['vakifStatus'], \PDO::PARAM_INT);
        
                $qry->execute();
        
                return [
                    'status' => true,
                    'data' => [],
                    'message' => 'Başvurunuz tamamlanmıştır'
                ];
            } catch (\PDOException $th) {
                var_dump($th->getMessage());
            }
    }
    
    public function applicationDetailByPreApplicationId($preApplicationId) {
        $sql = "SELECT * FROM " . $this->table . " WHERE pre_application_id = :preApplicationId LIMIT 1";
        $qry = $this->db->prepare($sql);
        $qry->bindValue(':preApplicationId', $preApplicationId, \PDO::PARAM_INT);
        $qry->execute();
        
        return $qry->fetch(\PDO::FETCH_ASSOC);
    }
}