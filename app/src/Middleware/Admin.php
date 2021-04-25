<?php

namespace App\Middleware;

class Admin extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->auth->check() || !$this->auth->hasAccess('dashboard.*')) {
            $this->flash->addMessage('danger', 'Kamu Tidak Bisa Mengakses Halaman Ini');
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $next($request, $response);
    }
}
