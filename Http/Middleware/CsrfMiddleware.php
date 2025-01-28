<?php 

namespace Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CsrfMiddleware {
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $session = $request->getAttribute('session');
        $csrfToken = $request->getParsedBody()['csrf_token'] ?? '';

        if ($this->isTokenValid($csrfToken, $session['csrf_token'] ?? '')) {
            return $next($request, $response);
        }

        return $response->withStatus(403);
    }

    private function isTokenValid($submittedToken, $sessionToken)
    {
        return $submittedToken === $sessionToken;
    }
}