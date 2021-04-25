<?php

namespace App\Middleware;

use App\Model\Users;

class BkolPerusahaanCheck extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if ($this->auth->check()) {
            $user = $this->auth->check();
            $err = 0;
            
            if (!$user->email || $user->email == "") {
                $err++;
            }

            if ($err) {
                $this->flash->addMessage(
                    'warning',
                    'Ups! Tampaknya Profile Anda kehilangan beberapa informasi..'.
                        '  Harap tinjau / perbaiki profil Anda di bawah ini untuk melanjutkan.'
                );
                return $response->withRedirect($this->router->pathFor('dashboardperusahaan-profile'));
            }
        }
        
        return $next($request, $response);
    }
}
