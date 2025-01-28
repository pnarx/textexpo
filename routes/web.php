<?php

// Ana Sayfa Rotası

$publicPath = __DIR__ . '/../public';

use Http\Controllers\HomeController;
use Http\Middleware\CsrfMiddleware;
use Http\Middleware\CheckAuthMiddleware;
use Http\Middleware\CheckApplicationToken;
use Slim\Routing\RouteCollectorProxy;
use Slim\Exception\HttpNotFoundException;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterfac as ServerRequest;

$app->get('/', HomeController::class . ':index');
$app->post(
    '/stand-basvuru', 
    HomeController::class.':saveApplication'
    )
    ->setName('applicationSave');
$app->get('/basvuruadmin', HomeController::class.':login');
$app->post('/login', [HomeController::class, 'loginStore']);

$app->group('/basvuruadmin', function (RouteCollectorProxy $group) {
    $group->get('/logout', [HomeController::class, 'logout'])->setName('logout');
    $group->get('/basvuru-surecindekiler', [HomeController::class, 'continuingApplicaiton'])
        ->setName('continuingApplication');
    $group->get('/on-basvurular', [HomeController::class, 'pendingApplication'])
        ->setName('preApplication');
    $group->get('/basvuru-surec-detay/{token}', [HomeController::class, 'applicationDetail'])
        ->setName('applicationDetail');
        
    $group->get('/get-pre-application', [HomeController::class, 'getPreApplications'])
        ->setName('getPreApplications');
    $group->post('/approval-pre-application', [HomeController::class, 'approvalPreApplication'])
        ->setName('approvalPreApplication');
    $group->post('/get-pre-application-detail', [HomeController::class, 'getPreApplicationDetail'])
        ->setName('getPreApplicationDetail');
        
    $group->post('/deny-application', [HomeController::class, 'denyApproval'])
        ->setName('denyApproval');
    $group->get('/raporlar', [HomeController::class, 'raportPage'])->setName('raport');
    $group->get('/smtp-ayari', [HomeController::class, 'smtpAyari'])->setName('smtpAyari');
    $group->post('/template-store', [HomeController::class, 'templateStore'])->setName('templateStore');
    $group->post('/smtp-store', [HomeController::class, 'smtpStore'])->setName('smtpStore');
})->add(new CheckAuthMiddleware);


$app->get('/basvuru-tamamla/{token}', [HomeController::class, 'doneApplication']);
$app->post('/basvuru-tamamla', [HomeController::class, 'doneApplicationStore'])
    ->setName('doneApplicationStore');
$app->get('/evrak-tamamla/{applicationToken}', [HomeController::class, 'evrakTamamla'])
    ->setName('evrakTamamla');
$app->post('/evrak-tamamla', [HomeController::class, 'evrakTamamlaStore'])
    ->setName('evrakTamamlaStore');
$app->get('/export-pdf/{token}', [HomeController::class, 'exportPdf'])
    ->setName('exportPdf');

