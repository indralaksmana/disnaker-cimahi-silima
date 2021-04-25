<?php

namespace App\Middleware;

class AgendaCheck extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->config['agenda-enabled']) {
            $this->flash->addMessage('danger', 'The job is not enabled for this site!');

            if (strpos($request->getUri()->getPath(), 'dashboard') !== false) {
                return $response->withRedirect($this->router->pathFor('dashboard'));
            }
            
            return $response->withRedirect($this->router->pathFor('home'));

        }

        return $next($request, $response);
    }
}
