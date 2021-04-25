<?php

// Blog Front End
$app->group('/berita', function () use ($app) {
    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}', 'Minisite:DetailBerita')
        ->setName('detail-berita');

    $this->map(['GET', 'POST'], '/kategori/{slug}/', 'Minisite:DetailKategori')
        ->setName('detail-kategori');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\BlogCheck($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));

$app->group('/agenda', function () use ($app) {
    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}', 'Minisite:DetailAgenda')
        ->setName('agenda-detail');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\BlogCheck($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));

$app->group('/album', function () use ($app) {
    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}', 'Minisite:DetailGallery')
        ->setName('album-detail');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\BlogCheck($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));