<?php 

namespace Http\Controllers;

use App\Email\SendApplicationMail;
use App\Email\ApprovalPreApplicationMail;
use Http\CsrfToken;
use Http\Mail;
use Http\Models\Application;
use Http\Models\ApplicationDetail;
use Http\Models\User;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Http\Models\RentedStandAreaPaymentPlan;
use Http\Models\RentedStandAreaDetail;
use Http\Models\Model;
use Http\Models\SmtpConf;
use Http\Models\RentedStandArea;
use Http\Models\EmailTemplates;
use Http\Models\Report;

class HomeController extends Controller {
    public function index(Request $request, Response $response, $args) {
        $users = new User;

        return $this->view($response, 'welcome.twig');
    }

    public function saveApplication($request, Response $response, $args) {
        $data = $request->getParsedBody();

        $PreApplicationDTO = [
            'full_name' => $data['form'][0]['value'],
            'company_name' => $data['form'][1]['value'],
            'duty' => $data['form'][2]['value'],
            'company_website' => $data['form'][3]['value'],
            'email' => $data['form'][4]['value'],
            'company_phone' => $data['form'][5]['value'],
            'mobile_phone' => $data['form'][6]['value'],
            'hear_about_us' => $data['form'][7]['value'],
            'message' => $data['form'][8]['value'],
        ];

        $application = new Application;
        $application->saveApplication($PreApplicationDTO);
        $emailTemplates = new EmailTemplates;
        $stepOneTemplate = $emailTemplates->getTemplateByStepNumber(1);
        (new Mail())
            ->mail()
            ->addAddress($PreApplicationDTO['email'], $PreApplicationDTO['full_name'])
            ->addAttachment('../app/storage/email_attachments/test-saha-basvuru.pdf')
            ->body((new SendApplicationMail('Ön Başvuru', $stepOneTemplate['template']))->handle($PreApplicationDTO))
            ->send();

        // JSON olarak döndür
        $response->getBody()->write(json_encode([
            "status" => true,
            "data" => [],
            "message" => "Başvurunuz sistemimize başarılı bir şekilde gelmiştir. Bilgilendirmeler mailinize gönderilecektir."
        ]));
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login($request, Response $response) {
        return $this->twig->render($response, 'login.twig');
    }
    public function loginStore($request, Response $response) {
        session_start();
        $data = $request->getParsedBody();
        $nickname = $data['form'][0]['value'];
        $password = $data['form'][1]['value'];
        
        $user = new User;
        $getUser = $user->user($nickname);
        if (!password_verify($password, $getUser['password'])) {
            $response->getBody()->write(json_encode([
                "status" => false,
                "data" => [],
                "message" => "Kullanıcı bilgileri yanlış",
            ]));
        
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        if ($user->attempt($nickname, $password)) {
            $_SESSION['user'] = $getUser;
        }
        
        $response->getBody()->write(json_encode([
            "status" => true,
            "data" => [],
            "message" => "Kullanıcı başarılı bir şekilde kayıt oldu",
        ]));
    
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function logout($request, Response $response) {
        session_start();
        
        session_destroy();
        unset($_SESSION['user']);
        
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
    public function continuingApplicaiton($request, Response $response) {
        $application = new Application();
        $continuingApplicaiton = $application->continuingApplicaiton();
        
        return $this->twig->render($response, 'continuing_application.twig', [
            'applications' => $continuingApplicaiton
        ]);
    }
    public function pendingApplication($request, Response $response) {
        return $this->twig->render($response, 'pending_application.twig');
    }
    public function getPreApplications($request, Response $response, $args) {
        $page = isset($_GET['page']) ? $_GET['page'] : 0;
        
        $application = new Application;
        $preApplications = $application->getPreApplications();
        
        if (!$preApplications['status']) {
            $response->getBody()->write(json_encode([
                "status" => $preApplications['status'],
                "data" => $preApplications['data'],
                "message" => $preApplications['message'],
            ]));
            
            return $response->withHeader('Content-type', 'application/json');
        }
        
        $collect = [];

        foreach ($preApplications['data'] as $application) {
            $user = new User;
            $userDetail = $user->getUserById($application->response_user_id);
        
            $collect[] = ['pre_application' => $application, 'user' => $userDetail];
        }
        
        $response->getBody()->write(json_encode([
            "status" => $preApplications['status'],
            "data" => $collect,
            "message" => $preApplications['message'],
        ]));
        
        return $response->withHeader('Content-type', 'application/json');
    }
    public function getPreApplicationDetail($request, Response $response) {
        $data = $request->getParsedBody();
        $applicationId = $data['applicationId'];
        
        $application = new Application;
        $preApplications = $application->getPreApplicationDetail($applicationId);
        
        if (!$preApplications['status']) {
            $response->getBody()->write(json_encode([
                "status" => $preApplications['status'],
                "data" => $preApplications['data'],
                "message" => $preApplications['message'],
            ]));
            
            return $response->withHeader('Content-type', 'application/json');
        }
        
        $response->getBody()->write(json_encode([
            "status" => $preApplications['status'],
            "data" => $preApplications['data'],
            "message" => $preApplications['message'],
        ]));
        
        return $response->withHeader('Content-type', 'application/json');
        
    }
    public function applicationDetail($request, Response $response, $args) {
         $token = $args['token'];
         if (!isset($token)) {
              $response->getBody()->write(json_encode([
                "status" => false,
                "data" => [],
                "message" => 'token olmalıdır',
            ]));
            
            return $response->withHeader('Content-type', 'application/json');
         }
         $checkApplicationMiddleware = new \Http\Middleware\CheckApplicationToken;
         
         $middleware = $checkApplicationMiddleware->handle($token);
         
         if($middleware) {
             $response->getBody()->write(json_encode([
                "status" => false,
                "data" => [],
                "message" => 'token geçersiz',
            ]));
            
            return $response->withHeader('Content-type', 'application/json');
         }
         
         
         $application = new Application;
         $applicationDetail = $application->getPreApplicationWithDetail($token);
         $rentedStandArea = new RentedStandArea; 
         $rentedStandAreaData = $rentedStandArea->getByApplicationId($applicationDetail['pre_application']['id']);
        
         return $this->twig->render($response, 'basvuru-surec-detay.twig', [
            'application' => $applicationDetail,
            'rentedStandAreaData' => $rentedStandAreaData
         ]);
    }
    public function approvalPreApplication($request, Response $response) {
        $data = $request->getParsedBody();
        $applicationId = $data['applicationId'];

        if (!isset($applicationId)) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'data' => [],
                'message' => 'applicationId zorunludur'
            ]));
            
            return $response;
        }
        
        $application = new Application;
        $approvalApplication = $application->approvalPreApplication($applicationId);
        
        if (!$approvalApplication['status']) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'data' => [],
                'message' => 'Ön başvuru onaylama sırasında bir hata oluştu. Lütfen daha sonra yeniden deneyiniz'
            ]));
            
            return $response;
        }
        
        $preApplicationById = $application->getPreApplicationById($applicationId);
        $MailDTO = [
            "full_name" => $preApplicationById['full_name'],
            "company_name" => $preApplicationById['company_name'],
            "token" => $preApplicationById['token'],
            "full_name" => $preApplicationById['full_name'],
            "email" => $preApplicationById['email']
        ];
        
        $emailTemplates = new EmailTemplates;
        $stepTwoTemplate = $emailTemplates->getTemplateByStepNumber(2);
        


        (new Mail())
            ->mail()
            ->addAddress($MailDTO['email'], $MailDTO['full_name'])
            ->body((new ApprovalPreApplicationMail('Ön Başvuru Bilgilendirme', $stepTwoTemplate['template']))->handle($MailDTO))
            ->send();

        $response->getBody()->write(json_encode([
            "status" => true,
            "data" => [],
            "message" => 'Firma onaylandı',
        ]));
        
        return $response->withHeader('Content-type', 'application/json');
    }
    public function doneApplication($request, Response $response, $args) {
        $token = $args['token'];

        $application = new Application;
        $preApplicationByToken = $application->preApplicationByToken($token);
        
        if (!$preApplicationByToken) {
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }
        
        $applicationDetail = new ApplicationDetail;
        $applicationDetailByPreApplicationId = $applicationDetail->applicationDetailByPreApplicationId($preApplicationByToken['id']);

        return $this->twig->render($response, 'basvuru-tamamla.twig', [
            'application' => $preApplicationByToken,
            'applicationDetail' => $applicationDetailByPreApplicationId
        ]);
    }
    public function doneApplicationStore($request, Response $response, $args) {
        $data = $request->getParsedBody();
        
        $memberStatus = $data['form'][24]['value'] == 'yes' ? 'Evet' : 'Hayır';
        $expoSales = $data['form'][25]['value'] == 'yes' ? 'Evet' : 'Hayır';
        
        $applicationDetailDTO = [
            "pre_application_id" => $data['applicationId'],
            "address" => $data['form'][1]['value'],
            "district" => $data['form'][2]['value'],
            "city" => $data['form'][3]['value'],
            "country" => $data['form'][4]['value'],
            "companyEmail" => $data['form'][7]['value'],
            "companyManager" => $data['form'][8]['value'],
            "companyRepresentative" => $data['form'][9]['value'],
            "companyManagerPhone" => $data['form'][10]['value'],
            "responsibleEmail" => $data['form'][11]['value'],
            "taxNumber" => $data['form'][12]['value'],
            "taxOffice" => $data['form'][13]['value'],
            "vakifStatus" => $data['form'][14]['value'],
            "expoManagerTitle" => $data['form'][15]['value'],
            "expoManagerPhone" => $data['form'][16]['value'],
            "expoManagerEmail" => $data['form'][17]['value'],
            "financeManagerTitle" => $data['form'][18]['value'],
            "financeManagerPhone" => $data['form'][19]['value'],
            "financeManagerEmail" => $data['form'][20]['value'],
            "naceNo" => $data['form'][21]['value'],
            "mersisNo" => $data['form'][22]['value'],
            "kepAddress" => $data['form'][23]['value'],
            "companyStatus" => $data['form'][24]['value'],
            "memberStatus" => $memberStatus,
            "expoSales" => $expoSales,
        ];
        
        $applicationDetail = new ApplicationDetail;
        $applicationDetail->saveDetailByApplicationId($applicationDetailDTO);
        
        $response->getBody()->write(json_encode([
            "status" => true,
            "data" => [],
            "message" => 'Firma onaylandı',
        ]));
        
        return $response->withHeader('Content-type', 'application/json');
    }
    public function evrakTamamla($request, Response $response, $args) {
        $applicationToken = $args['applicationToken'];
        $application = new Application;
        $applicationDetail = new ApplicationDetail;
        $preApplicationByToken = $application->preApplicationByToken($applicationToken);
        $rentedStandArea = new RentedStandArea();
        $rentedStandAreaPaymentPlan = new RentedStandAreaPaymentPlan();
        $rentedStandAreaDetail = new RentedStandAreaDetail();
        $applicationDetail = $applicationDetail->applicationDetailByPreApplicationId($preApplicationByToken['id']);
        
        $rentedStandAreaData = $rentedStandArea->getByApplicationId($preApplicationByToken['id']);
        $rentedStandAreaDetailData = @$rentedStandAreaDetail->getByApplicationId($rentedStandAreaData['id']);
        $rentedStandAreaPaymentData = @$rentedStandAreaPaymentPlan->getByApplicationId($rentedStandAreaData['id']);
        
        return $this->twig->render($response, 'evrak-tamamla.twig', [
            'application' => $preApplicationByToken,
            'rentedStandAreaData' => $rentedStandAreaData,
            'rentedStandAreaDetailData' => $rentedStandAreaDetailData,
            'rentedStandAreaPaymentData' => $rentedStandAreaPaymentData,
            'applicationDetail' => $applicationDetail
        ]);
    }
    
    public function evrakTamamlaStore($request, Response $response) {
        $data = $request->getParsedBody();
        
        $DTO = [
            "stant_salon_numarasi" => $data["salon_and_stant_no"],
            "ozel_dekarasyon_brim" => $data["ozel_dekerasyon"],
            "ozel_dekarasyon_fiyat" => $data["ozel_dekerasyon_price"],
            "ozel_dekarasyon_toplam_fiyat" => $data["ozel_dekerasyon_total_price"],
            "sponsorluk_brim" => $data["sponsorluk"],
            "sponsorluk_fiyat" => $data["sponsorluk_price"],
            "sponsorluk_toplam_fiyat" => $data["sponsorluk_total_price"],
            "meydana_bakan_cephe_farki_brim" => $data["meydana_bakan_cephe_farkı"],
            "meydana_bakan_cephe_farki_fiyat" => $data["meydana_bakan_cephe_farkı_price"],
            "meydana_bakan_cephe_farki_toplam_fiyat" => $data["meydana_bakan_cephe_farkı_total_price"],
            "cift_kat_stand_farki_brim" => $data["cift_kat_farkı"],
            "cift_kat_stand_farki_fiyat" => $data["cift_kat_farkı_price"],
            "cift_kat_stand_farki_toplam_fiyat" => $data["cift_kat_farkı_total_price"],
            "dis_alan_brim" => $data["dis_alan"],
            "dis_alan_fiyat" => $data["dis_alan_price"],
            "dis_alan_toplam_fiyat" => $data["dis_alan_total_price"],
            "hizmet_bedelleri_icerigi" => $data["hizmet_bedelleri_icerigi"],
            "m2_basina_hizmet_bedeli" => $data["m2_basina_hizmet_bedeli"],
            "m2_basina_katik_atik_bedeli" => $data["m2_basina_katik_atik_bedeli"],
            "bir_yillik_online_hizmet_bedeli" => $data["bir_yillik_online_hizmet_bedeli"],
            "toplam_tl" => $data["toplam_tl"],
            "kdv" => $data["kdv"],
            "sozlesme_damga_vergisi" => $data["sozlesme_damga_vergisi"],
            "genel_toplam_tl" => $data["genel_toplam_tl"],
            "pesinat" => $data['hepsi_odendi'] ? $data["genel_toplam_tl"] : $data["pesinat"],
            "bir_taksit" => $data["bir_taksit"],
            "iki_taksit" => $data["iki_taksit"],
            "uc_taksit" => $data["uc_taksit"],
            "dort_taksit" => $data["dort_taksit"],
            "pre_application_id" => $data["application_id"],
            "hepsiOdendi" => $data['hepsi_odendi']
        ];
        
        $rentedStandArea = new RentedStandArea;
        $rentedStandAreaDetail = new RentedStandAreaDetail;
        $rentedStandAreaPaymentPlan = new RentedStandAreaPaymentPlan;
        $application = new Application;
        $appData = $application->getPreApplicationById($DTO['pre_application_id']);
        
        $saveRentedStandArea = $rentedStandArea->updateOrCreate($DTO);
        $rentedStandAreaDetail->updateOrCreate($DTO, $saveRentedStandArea['data']['rented_stand_area_id']);
        $rentedStandAreaPaymentPlan->updateOrCreate($DTO, $saveRentedStandArea['data']['rented_stand_area_id']);

        return $response
            ->withHeader('Location', '/evrak-tamamla/'.$appData['token'])
            ->withStatus(302);
    }
    
    public function exportPdf($request, Response $response, $args) {
        header('Content-Type: text/html; charset=UTF-8');
        $applicationToken = $args['token'];

        $preApplicationSql = "select * from pre_application_stand WHERE token = :token";
        $preApplicationQry = (new Model)->db->prepare($preApplicationSql);
        $preApplicationQry->bindValue(':token', $applicationToken, \PDO::PARAM_STR);
        $preApplicationQry->execute();
        $preApplicationData = $preApplicationQry->fetch(\PDO::FETCH_ASSOC);
        
        $preApplicationDetailSql = "select * from pre_application_detail_stand WHERE pre_application_id = :id";
        $preApplicationDetailQry = (new Model)->db->prepare($preApplicationDetailSql);
        $preApplicationDetailQry->bindValue(':id', $preApplicationData['id'], \PDO::PARAM_INT);
        $preApplicationDetailQry->execute();
        $preApplicationDetailData = $preApplicationDetailQry->fetch(\PDO::FETCH_ASSOC);
        
        $rentedStandAreaSql = "select * from rented_stand_area WHERE pre_application_id = :id";
        $rentedStandAreaQry = (new Model)->db->prepare($rentedStandAreaSql);
        $rentedStandAreaQry->bindValue(':id', $preApplicationData['id'], \PDO::PARAM_INT);
        $rentedStandAreaQry->execute();
        $rentedStandAreaData = $rentedStandAreaQry->fetch(\PDO::FETCH_ASSOC);
        
        $rentedStandAreaDetailSQL = "select * from rented_stand_area_detail WHERE rented_stand_area_id = :id";
        $rentedStandAreaDetailQry = (new Model)->db->prepare($rentedStandAreaDetailSQL);
        $rentedStandAreaDetailQry->bindValue(':id', $rentedStandAreaData['id'], \PDO::PARAM_INT);
        $rentedStandAreaDetailQry->execute();
        $rentedStandAreaDetailData = $rentedStandAreaDetailQry->fetch(\PDO::FETCH_ASSOC);
        
        $rentedStandAreaPaymentSQL = "select * from rented_stand_area_payment_plan WHERE rented_stand_area_id = :id";
        $rentedStandAreaPaymentQry = (new Model)->db->prepare($rentedStandAreaPaymentSQL);
        $rentedStandAreaPaymentQry->bindValue(':id', $rentedStandAreaData['id'], \PDO::PARAM_INT);
        $rentedStandAreaPaymentQry->execute();
        $rentedStandAreaPaymentData = $rentedStandAreaPaymentQry->fetch(\PDO::FETCH_ASSOC);
        
        $kdvHesapla = $preApplicationDetailData['vakif_status'] ? '10%' : '20%';
        
        // taksit seçeneği hesaplama
        $totalColspan = 15;
        $getPlanColumnsSQL = "select pesinat, bir_taksit, iki_taksit, uc_taksit, dort_taksit from rented_stand_area_payment_plan WHERE rented_stand_area_id = :id";
        $getPlanColumnsQRY = (new Model)->db->prepare($getPlanColumnsSQL);
        $getPlanColumnsQRY->bindValue(':id', $rentedStandAreaData['id'], \PDO::PARAM_INT);
        $getPlanColumnsQRY->execute();
        $getPlanColumnsData = $getPlanColumnsQRY->fetch(\PDO::FETCH_ASSOC);

        if ($getPlanColumnsData) {
            $filledColumns = array_filter($getPlanColumnsData, function($value) {
                return !empty($value);
            });
        }

    
        $filledCount = !isset($filledColumns) ? $totalColspan : count($filledColumns);
        if ($filledCount != 0) {
            $colspanPerColumn = $totalColspan / $filledCount;
        } else {
            $colspanPerColumn = $filledColumns;
        }
        
        $columnContent = '';
        $columnTitle = '';

        foreach ($getPlanColumnsData as $key => $value):
            if (!empty($value)):
                $columnTitle .= '<td class="h12 text-center" colspan="'.$colspanPerColumn.'">';
                    $columnTitle .= ucfirst(str_replace('_', ' ', $key));
                $columnTitle .= '</td>';
                $columnContent .= '<td class="h12 text-center" colspan="'.$colspanPerColumn.'">';
                    $columnContent .= $value;
                $columnContent .= '</td>';
            endif;
        endforeach;

        $collect = [];
        
        $collect['pre_application'] = $preApplicationData;
        $collect['pre_application_detail'] = $preApplicationDetailData;
        $collect['rented_stand_area'] = $rentedStandAreaData;
        $collect['rented_stand_area_detail'] = $rentedStandAreaDetailData;
        $collect['rented_stand_area_payment_plan'] = $rentedStandAreaPaymentData;
        
        $html = file_get_contents('../app/pdf_templates/stant_sozlesmesi.phtml');
        
        $html = str_replace('{{firma_adi}}', $collect["pre_application"]['company_name'], $html);
        $html = str_replace('{{adresi}}', $collect["pre_application_detail"]['address'], $html);
        $html = str_replace('{{ilce}}', $collect["pre_application_detail"]['district'], $html);
        $html = str_replace('{{is_tel}}', $collect["pre_application"]['company_phone'], $html);
        $html = str_replace('{{sehir}}', $collect["pre_application_detail"]['city'], $html);
        $html = str_replace('{{ulke}}', $collect["pre_application_detail"]['country'], $html);
        $html = str_replace('{{web_sitesi}}', $collect["pre_application"]['company_web_site'], $html);
        $html = str_replace('{{sirket_eposta}}', $collect["pre_application_detail"]['company_email'], $html);
        $html = str_replace('{{firma_yetkilisi}}', $collect["pre_application_detail"]['company_representative'], $html);
        $html = str_replace('{{unvani}}', $collect["pre_application_detail"]['company_official_title'], $html);
        $html = str_replace('{{cep_tel}}', $collect["pre_application_detail"]['company_manager_phone'], $html);
        $html = str_replace('{{yetkili_eposta}}', $collect["pre_application_detail"]['company_manager_email'], $html);
        $html = str_replace('{{vergi_no}}', $collect["pre_application_detail"]['tax_number'], $html);
        $html = str_replace('{{kdv_orani}}', $kdvHesapla, $html);
        $html = str_replace('{{vergi_dairesi}}', $collect["pre_application_detail"]['tax_office'], $html);
        $html = str_replace('{{fuar_sorumlusu_unvani}}', $collect["pre_application_detail"]['fair_manager'], $html);
        $html = str_replace('{{fair_manager_mobile_phone}}', $collect["pre_application_detail"]['fair_manager_mobile_phone'], $html);
        $html = str_replace('{{fair_manager_email}}', $collect["pre_application_detail"]['fair_manager_email'], $html);
        
        $html = str_replace('{{finans_sorumlusu_title}}', $collect["pre_application_detail"]['finance_officer_title'], $html);
        $html = str_replace('{{fuar_sorumlusu_cep_tel}}', $collect["pre_application_detail"]['finance_officer_phone'], $html);
        $html = str_replace('{{fuar_sorumlusu_eposta}}', $collect["pre_application_detail"]['finance_officer_email'], $html);
        
        $html = str_replace('{{nace_kod}}', $collect["pre_application_detail"]['nace_code'], $html);
        $html = str_replace('{{mersis_no}}', $collect["pre_application_detail"]['mersis_number'], $html);
        $html = str_replace('{{kep_adresi}}', $collect["pre_application_detail"]['kep_address'], $html);
        $html = str_replace('{{mikro_kobs_kobi_ustu}}', $collect["pre_application_detail"]['mikro_obi'], $html);
        $html = str_replace('{{saha_istanbul_uyelik_durumu}}', $collect["pre_application_detail"]['saha_istanbul_member_status'], $html);
        $html = str_replace('{{saha_expo_satis}}', $collect["pre_application_detail"]['saha_expo_sales'], $html);
        $html = str_replace('{{salon_stant_no}}', $collect["rented_stand_area"]['stant_salon_numarasi'], $html);
        
        $html = str_replace('{{ozel_dekorasyon_brim}}', $collect["rented_stand_area"]['ozel_dekarasyon_brim'], $html);
        $html = str_replace('{{ozel_dekarasyon_fiyat}}', $collect["rented_stand_area"]['ozel_dekarasyon_fiyat'], $html);
        $html = str_replace('{{ozel_dekarasyon_toplam_fiyat}}', $collect["rented_stand_area"]['ozel_dekarasyon_toplam_fiyat'], $html);
        $html = str_replace('{{sponsorluk_brim}}', $collect["rented_stand_area"]['sponsorluk_brim'], $html);
        $html = str_replace('{{sponsorluk_fiyat}}', $collect["rented_stand_area"]['sponsorluk_fiyat'], $html);
        $html = str_replace('{{sponsorluk_toplam_fiyat}}', $collect["rented_stand_area"]['sponsorluk_toplam_fiyat'], $html);
        $html = str_replace('{{meydana_bakan_cephe_farki_brim}}', $collect["rented_stand_area"]['meydana_bakan_cephe_farki_brim'], $html);
        $html = str_replace('{{meydana_bakan_cephe_farki_fiyat}}', $collect["rented_stand_area"]['meydana_bakan_cephe_farki_fiyat'], $html);
        $html = str_replace('{{meydana_bakan_cephe_farki_toplam_fiyat}}', $collect["rented_stand_area"]['meydana_bakan_cephe_farki_toplam_fiyat'], $html);
        $html = str_replace('{{cift_kat_stant_farki_brim}}', $collect["rented_stand_area"]['cift_kat_stand_farki_brim'], $html);
        $html = str_replace('{{cift_kat_stant_farki_fiyat}}', $collect["rented_stand_area"]['cift_kat_stand_farki_fiyat'], $html);
        $html = str_replace('{{cift_kat_stant_farki_toplam_fiyat}}', $collect["rented_stand_area"]['cift_kat_stand_farki_toplam_fiyat'], $html);
        $html = str_replace('{{dis_alan_brim}}', $collect["rented_stand_area"]['dis_alan_brim'], $html);
        $html = str_replace('{{dis_alan_fiyat}}', $collect["rented_stand_area"]['dis_alan_fiyat'], $html);
        $html = str_replace('{{dis_alan_toplam_fiyat}}', $collect["rented_stand_area"]['dis_alan_toplam_fiyat'], $html);
        
        $html = str_replace('{{m2_basina_hizmet_bedeli}}', $collect["rented_stand_area_detail"]['m2_kare_basina_hizmet_bedeli'], $html);
        $html = str_replace('{{m2_basina_kati_atik_bedeli}}', $collect["rented_stand_area_detail"]['m2_basina_kati_atik_bedeli'], $html);
        $html = str_replace('{{bir_yillik_online_hizmet_bedeli}}', $collect["rented_stand_area_detail"]['bir_yillik_online_hizmet_bedeli'], $html);
        $html = str_replace('{{toplam}}', $collect["rented_stand_area_detail"]['bir_yillik_online_hizmet_bedeli'], $html);
        $html = str_replace('{{kdv}}', $collect["rented_stand_area_detail"]['kdv_tutari'], $html);
        $html = str_replace('{{damga_vergisi}}', $collect["rented_stand_area_detail"]['sozlesme_damga_vergisi'], $html);
        $html = str_replace('{{genel_toplam}}', $collect["rented_stand_area_detail"]['genel_toplam'], $html);
        $html = str_replace('{{hizmet_sozlesmesi_icerigi}}', $collect["rented_stand_area_detail"]['hizmet_bedelleri_icerigi'], $html);
        
        $html = str_replace('{{salon_ve_stand_no}}', $collect['rented_stand_area']['stant_salon_numarasi'], $html);
        
        $html = str_replace('{{pesinat}}', $collect["rented_stand_area_payment_plan"]['pesinat'], $html);
        $html = str_replace('{{bir_taksit}}', $collect["rented_stand_area_payment_plan"]['bir_taksit'], $html);
        $html = str_replace('{{iki_taksit}}', $collect["rented_stand_area_payment_plan"]['iki_taksit'], $html);
        $html = str_replace('{{uc_taksit}}', $collect["rented_stand_area_payment_plan"]['uc_taksit'], $html);
        $html = str_replace('{{dort_taksit}}', $collect["rented_stand_area_payment_plan"]['dort_taksit'], $html);
        $html = str_replace('{{column_content}}', $columnContent, $html);
        $html = str_replace('{{column_title}}', $columnTitle, $html);
        
        
        $pdf = new \TCPDF();
    
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetHeaderData('', 0, '', '', array(0,0,0), array(255,255,255));
        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('stant_sozlesme.pdf', 'D');
        
        return $response;
    }
    
    public function denyApproval($request, Response $response) {
        $data = $request->getParsedBody();
        $applicationId = $data['applicationId'];
        
        /** modele çekilecek */
        
        $sql = "UPDATE pre_application_stand SET status = :status WHERE id = :id";
        $qry = (new Model)->db->prepare($sql);
        $qry->bindValue('status', 'inactive', \PDO::PARAM_STR);
        $qry->bindValue('id', $applicationId, \PDO::PARAM_INT);
        $qry->execute();
        
        $response->getBody()->write(json_encode([
            "status" => true,
            "data" => [],
            "message" => 'Firma reddedildi',
        ]));
        
        return $response->withHeader('Content-type', 'application/json');
    }
    
    public function raportPage(Request $request, Response $response, $args) {
        /**
         * 
         * Eğer bir sayfalama gerekiyor ise bu parametreleri modele göndermeniz gerekiyor
         * gönderilen modelde sql sorgusuna limit ve offset değerlerine vererek sayfalama yapabilirsiniz.
         * 
        $requestParams = $request->getQueryParams();
        $page = $requestParams['page'] ?? 1;
        $size = $requestParams['size'] ?? 20;
        */
        
        
        $rentedStandAreaPaymentPlan = new RentedStandAreaPaymentPlan;
        $getReportPlan = $rentedStandAreaPaymentPlan->getTaksit();
        
        $currentDate = (new \DateTime())->format("Y-m-d");
        
        $raportCollect = [];
        foreach($getReportPlan as $plan) {
            if ($plan['hepsi_odendi'] == 1) {
                $raportCollect[] = ["type" => "all", "data" => $plan, "past" => null, "future" => null, "installmentCount" => 0];
            }
            
            if ($plan['hepsi_odendi'] == 0) {
                $birTaksitTarih = !is_null($plan['bir_taksit']) && !empty($plan['bir_taksit']) ? (new \DateTime($plan['bir_taksit']))->format("Y-m-d") : null;
                $ikiTaksitTarih = !is_null($plan['iki_taksit']) && !empty($plan['iki_taksit']) ? (new \DateTime($plan['iki_taksit']))->format("Y-m-d") : null;
                $ucTaksitTarih = !is_null($plan['uc_taksit']) && !empty($plan['uc_taksit']) ? (new \DateTime($plan['uc_taksit']))->format("Y-m-d") : null;
                $dortTaksitTarih = !is_null($plan['dort_taksit']) && !empty($plan['dort_taksit']) ? (new \DateTime($plan['dort_taksit']))->format("Y-m-d") : null;
                $taksitCount = 0;
                
                !is_null($plan['bir_taksit']) && !empty($plan['bir_taksit']) ? $taksitCount += 1 : null;
                !is_null($plan['iki_taksit']) && !empty($plan['iki_taksit']) ? $taksitCount += 1 : null;
                !is_null($plan['uc_taksit']) && !empty($plan['uc_taksit']) ? $taksitCount += 1 : null;
                !is_null($plan['dort_taksit']) && !empty($plan['dort_taksit']) ? $taksitCount += 1 : null;
                
                $raportDateCollect = [];
                if (!is_null($birTaksitTarih) && !empty($plan['bir_taksit']) && $birTaksitTarih < $currentDate) {
                    $raportDateCollect['past'][] = $birTaksitTarih;
                } else {
                    $raportDateCollect['future'][] = $birTaksitTarih;
                }
                
                if (!is_null($ikiTaksitTarih) && !empty($plan['iki_taksit']) && $ikiTaksitTarih < $currentDate) {
                    $raportDateCollect['past'][] = $ikiTaksitTarih;
                } else {
                    $raportDateCollect['future'][] = $ikiTaksitTarih;
                }
                
                if (!is_null($ucTaksitTarih) && !empty($plan['uc_taksit']) && $ucTaksitTarih < $currentDate) {
                    $raportDateCollect['past'][] = $ucTaksitTarih;
                } else {
                    $raportDateCollect['future'][] = $ucTaksitTarih;
                }
                
                if (!is_null($dortTaksitTarih) && !empty($plan['dort_taksit']) && $dortTaksitTarih < $currentDate) {
                    $raportDateCollect['past'][] = $dortTaksitTarih;
                } else {
                    $raportDateCollect['future'][] = $dortTaksitTarih;
                }
                
                $priceWithoutFormat = str_replace('.', '', $plan['genel_toplam']);
                $priceWithoutFormat = str_replace(',', '', $plan['genel_toplam']);
                $price = (float) $priceWithoutFormat; // sayıya dönüştür
                
                $price = $price - $plan['pesinat'];
                
                $installmentPrice = number_format($price / $taksitCount, 2);
                
                
                $raportCollect[] = ["type" => "installment", "data" => $plan, "past" => $raportDateCollect['past'] ?? null, "future" => $raportDateCollect['future'] ?? null, "installmentCount" => $taksitCount, "installmentPrice" => $installmentPrice, 'totalPrice' => $price];
            }
        }
        
        usort($raportCollect, function ($a, $b) {
            if ($a['future'] === null && $b['future'] === null) {
                return 0;
            }
        
            if ($a['future'] === null) {
                return 1;
            }
            if ($b['future'] === null) {
                return -1;
            }
            
            return strtotime($a['future'][0]) - strtotime($b['future'][0]);
        });
        
        return $this->view($response, 'raport.twig', ['raporlar' => $raportCollect]);
    }
    
    public function smtpAyari(Request $request, Response $response, $args) {
        $smtp = new SmtpConf;
        $data = $smtp->getSmtpConfByActive();
        
        $emailTemplates = new EmailTemplates;
        $allEamilTemplates = $emailTemplates->getEmailTemplatesByActive();
        
        return $this->twig->render($response, 'smtp_conf.twig', [
            'smtp' => $data,
            'allEamilTemplates' => $allEamilTemplates
        ]);
    }
    
    public function templateStore(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $templateId = $data['id'];
        $template = $data['template'];
        
        $emailTemplates = new EmailTemplates;
        $allEamilTemplates = $emailTemplates->updateById($templateId, $template);
        
        return $response
            ->withHeader('Location', '/basvuruadmin/smtp-ayari')
            ->withStatus(302);
    }
    
    public function smtpStore(Request $request, Response $response) {
        $data = $request->getParsedBody();
        
        $smtp = new SmtpConf;
        $updateSMTP = $smtp->updateSmtpConf($data);
        
        return $response
            ->withHeader('Location', '/basvuruadmin/smtp-ayari')
            ->withStatus(302);
    }
};