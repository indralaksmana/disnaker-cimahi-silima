<?php

namespace App\Middleware;

class Auth extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->auth->check()) {
            $this->flash->addMessage('danger', 'Kamu Harus Login Terlebih Dahulu ');
            return $response->withRedirect($this->router->pathFor('login'));
        }

        return $next($request, $response);
    }
}
