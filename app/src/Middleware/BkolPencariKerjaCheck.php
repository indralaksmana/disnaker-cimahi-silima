<?php

namespace App\Middleware;

use App\Model\Users;

class BkolPencariKerjaCheck extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if ($this->auth->check()) {
            $user = $this->auth->check();
            $err = 0;
            
            if (!$user->first_name || $user->first_name == "") {
                $err++;
            }
            if (!$user->last_name || $user->last_name == "") {
                $err++;
            }
            if (!$user->email || $user->email == "") {
                $err++;
            }

            if ($err) {
                $this->flash->addMessage(
                    'warning',
                    'Ups! Tampaknya Profile Anda kehilangan beberapa informasi..'.
                        '  Harap tinjau / perbaiki profil Anda di bawah ini untuk melanjutkan.'
                );
                return $response->withRedirect($this->router->pathFor('dashboardpencaker-biodata'));
            }
        }
        
        return $next($request, $response);
    }
}
