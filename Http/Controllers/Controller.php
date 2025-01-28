<?php
namespace Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class Controller {
    protected $twig;
    public function __construct(Twig $twig) {
        $this->twig = $twig;
    }
    public function lang($lang) {
        global $settings;
        $localization = $settings['localization'];
        $defaultLanguage = $localization['default'];
        $lang = in_array($lang, $localization['langs']) ? $lang : $defaultLanguage;
        
        return require_once __DIR__ . '/../../app/lang/'.$lang.'.php';
    }
    public function view(Response $response, $view, $data = []) {
        return $this->twig->render($response, $view, $data);
    }
}