<?php
// Blog Front End
$app->group('/disnaker-pelatihan', function () use ($app, $container) {
    $this->get('[/{page}]', 'Pelatihan:pelatihan')
        ->setName('disnaker-pelatihan');
    $this->get('/tutup/[/{page}]', 'Pelatihan:pelatihanTutup')
        ->setName('pelatihanTutup');
    $this->get('/seleksi/[/{page}]', 'Pelatihan:pelatihanSeleksi')
        ->setName('pelatihanSeleksi');


    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}', 'Pelatihan:pelatihanPost')
        ->setName('pelatihan-post');

    $this->map(['GET', 'POST'], '/modal-daftar-pelatihan/{id}/{slug}', 'Pelatihan:ModalDaftar')
        ->setName('pelatihan-modal-daftar');

    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}/daftar-pelatihan', 'Pelatihan:PelatihanDaftar')
        ->setName('pelatihan-post-daftar');

    $this->get('/author/{username}[/{page}]', 'Pelatihan:PelatihanAuthor')
        ->setName('blog-author');

    $this->get('/tag/{slug}[/{page}]', 'Pelatihan:pelatihanTag')
        ->setName('pelatihan-tag');

    $this->get('/kategori/{slug}[/{page}]', 'Pelatihan:pelatihanCategory')
        ->setName('pelatihan-category');

        $app->get('/pelatihanku/', 'Pelatihanku:index')
              ->setName('pelatihanku');
})
->add(new App\Middleware\BkolProfileCheck($container))
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PelatihanCheck($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
