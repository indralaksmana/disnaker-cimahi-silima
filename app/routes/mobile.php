<?php
// Non Logged Users
$app->group('/mobile', function () use ($app, $container, $settings) {
    // Home Page
    $this->map(['GET'], '', 'App:mobile')
        ->setName('mobile');

})
->add($container->get('csrf'))
->add(new App\Middleware\ Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
