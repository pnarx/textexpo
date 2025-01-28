<?php

use Http\Controllers\HomeController;
use Http\TwigHelpers;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\TwigFunction;

$container = $app->getContainer();
$container->set('twig', function () {
    $twig = Twig::create(__DIR__.'/../views/', [
        'cache' => false
    ]);
    $twig->getEnvironment()->addFunction(new TwigFunction('asset', [TwigHelpers::class, 'asset']));
    global $app;
    $twig->getEnvironment()->addFunction(new TwigFunction('urlFor', function ($name, $data = [], $queryParams = []) use ($app) {
        return $app->getRouteCollector()->getRouteParser()->urlFor($name, $data, $queryParams);
    }));

    return $twig;
});

$container->set(HomeController::class, function (ContainerInterface $container) {
    return new HomeController($container->get('twig'));
});