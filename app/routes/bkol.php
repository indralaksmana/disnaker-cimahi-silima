<?php

// Blog Front End
$app->group('', function () use ($app, $container) {
    $app->group('/pengajuan-ak1', function () use ($app) {
        $app->map(['GET', 'POST'], '', 'AK1:pengajuanak1')->setName('pengajuan-ak1');
        $app->map(['GET', 'POST'], '/laporan-akhir', 'AK1:pengajuanaklaporanakhir')->setName('laporan-pengajuan-ak1');
        $app->map(['GET', 'POST'], '/contoh-ak1', 'AK1:contohAk1')->setName('contoh-pengajuan-ak1');

        $app->map(['GET', 'POST'], '/upload-syarat', 'AK1:uploadSyarat')->setName('upload-syarat');
        
        $app->get('/peraturan', 'Bkol:kontak')->setName('kontak');
    })->add(new App\Middleware\Auth($container));

    $this->get('/companies/{companyname}[/{page}]', 'Bkol:jobCompanies')
        ->setName('job-companies');
    $app->group('/page', function () use ($app) {
        $app->get('/tentang', 'Bkol:tentang')->setName('tentang');
        $app->get('/kontak', 'Bkol:kontak')->setName('kontak');
        $app->get('/peraturan', 'Bkol:kontak')->setName('kontak');
    });
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));

// Blog Front End
$app->group('/lowongan-kerja', function () use ($app, $container) {
    $this->get('/[{page}]', 'Bkol:lowongan')
    ->setName('bkol-lowongan')->add(new App\Middleware\BkolProfileCheck($container));
    $this->get('/result/[{page}]', 'Bkol:searchResult')
        ->setName('searchbkol');
    // $this->get('/filter/[{page}]', 'Bkol:filterbkol')
    //     ->setName('filterbkol');
    $this->get('/category/{slug}[/{page}]', 'Bkol:jobCategory')
    ->setName('job-category');
    $this->get('/jabatan/{id}[/{page}]', 'Bkol:jobJabatan')
    ->setName('job-jabatan');
    $this->map(['GET', 'POST'], '/modal-lamaran/{id}/{slug}', 'Bkol:ModalApplication')
        ->setName('modal-lamaran');
    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}', 'Bkol:jobPost')
        ->setName('job_detail');
    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}/application', 'Bkol:jobPostApplication')
        ->setName('job_detail_application');
    $this->map(['GET', 'POST'], '/{year}/{month}/{day}/{slug}/application/lamaranterkirim', 'Bkol:jobPostApplication')
        ->setName('job_detail_application_terkirim');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
