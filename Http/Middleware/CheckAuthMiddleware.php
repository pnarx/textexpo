<?php 

namespace Http\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CheckAuthMiddleware {
    public function __invoke(Request $request, RequestHandler $handler) {
        session_start();
        $redirectUrl = '/basvuruadmin';
        
        if (!isset($_SESSION['user'])) {
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', $redirectUrl) // Yönlendirmek istediğiniz URL
                            ->withStatus(302);
        }
        
        $user = $_SESSION['user'];
        
        if (!isset($user)) {
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', $redirectUrl) // Yönlendirmek istediğiniz URL
                            ->withStatus(302);
        }
        
        return $handler->handle($request);
    }
}