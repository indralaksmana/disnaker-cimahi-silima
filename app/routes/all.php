<?php
// Non Logged Users
$app->group('', function () use ($app, $container, $settings) {
    // Home Page
    $this->map(['GET'], '/', 'App:home')
        ->setName('home');
    // Cron Jobs
    $this->map(['GET'], '/cron', 'Cron:run')
        ->setName('cron');
    $this->map(['GET'], '/tentang-bkol', 'App:tentang')
        ->setName('tentang-bkol');
     $this->map(['GET'], '/tentang-silima', 'App:tentang')
        ->setName('tentang-silima');
    
        $this->map(['GET'], '/offline', 'App:offline')
        ->setName('offline');

    // Contact
    $this->map(['GET', 'POST'], '/contact', 'App:contact')
        ->setName('contact');

    // CSRF
    $this->map(['GET'], '/csrf', 'App:csrf')
        ->setName('csrf');
    // Oauth
    $this->map(['GET'], '/oauth/{slug}', 'Oauth2:oauth2')
        ->setName('oauth');

})
->add($container->get('csrf'))
->add(new App\Middleware\ Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
// ->add(new App\Middleware\ProfileCheck($container));

// Maintenance Mode Bypasses All Middleware
$app->map(['GET'], '/maintenance', 'App:maintenance')
        ->setName('maintenance-mode')
        ->add(new App\Middleware\PageConfig($container))
        ->add(new App\Middleware\Seo($container));

// Assets Bypass All Middleware
$app->map(['GET'], '/asset', 'App:asset')
        ->setName('asset');

//Deployment
$app->map(['GET', 'POST'], '/' . $settings['deployment']['deploy_url'], 'Deploy:deploy')
    ->setName('deploy')
    ->add(new App\Middleware\Deploy($container));


// DAERAH
$app->get('/daerah/kabupatenkota/{provinsi}', 'Ajax:kabupatenkota')
        ->setName('kabupaten');
$app->get('/daerah/kecamatan/{provinsi}/{kabupatenkota}', 'Ajax:kecamatan')
        ->setName('kecamatan');
$app->get('/daerah/kelurahan/{provinsi}/{kabupatenkota}/{kecamatan}', 'Ajax:kelurahan')
        ->setName('kelurahan');

$app->get('/jenispendidikan/kodejurusan/{kodependidikan}', 'Ajax:KodeJurusan')
->setName('kodejurusan');

$app->get('/jenispendidikan/namajurusan/{kodependidikan}', 'Ajax:NamaJurusan')
->setName('namajurusan');

$app->get('/perusahaan/{perusahaan}', 'Ajax:perusahaan')
->setName('perusahaan-ajak');

// KBLI
$app->get('/kbli/golpokok/{kategori}', 'Kbli:GolPokok')
        ->setName('golpokok');
$app->get('/kbli/golongan/{kategori}/{golpokok}', 'Kbli:Golongan')
        ->setName('golongan');
$app->get('/kbli/subgolongan/{kategori}/{golpokok}/{golongan}', 'Kbli:SubGolongan')
        ->setName('subgolongan');
$app->get('/kbli/kelompok/{kategori}/{golpokok}/{golongan}/{subgolongan}', 'Kbli:Kelompok')
        ->setName('kelompok');


$app->get('/sk/bidang/{bidang}', 'SKeahlian:program')
        ->setName('sk-program');
$app->get('/sk/program/{bidang}/{progam}', 'SKeahlian:kompetensi')
        ->setName('sk-kompetensi');

