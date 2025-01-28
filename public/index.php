<?php


use DI\Container;
use Http\CsrfToken;
use Http\Middleware\TranslationMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Slim\Exception\HttpNotFoundException;


require_once '../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (
    Psr\Http\Message\ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails
) use ($app) {
    $twig = $app->getContainer()->get('twig'); // Twig servisini al
    $response = $app->getResponseFactory()->createResponse();
    
    // Twig ile 404 sayfasını render et
    return $twig->render($response, 'errors/404.twig', [
        'errorMessage' => 'Aradığınız sayfa bulunamadı.', // İsterseniz değişken ekleyebilirsiniz
    ])->withStatus(404);
});

$csrfToken = CsrfToken::generate();

$settings = require __DIR__ . '/../app/settings.php';
require_once __DIR__ . '/../app/dependencies.php';

$app->add(TwigMiddleware::createFromContainer($app, 'twig'));

// /** web sitesi için gerekli tüm rotalar burada */,
require_once __DIR__ . '/../routes/web.php';

$app->run();
