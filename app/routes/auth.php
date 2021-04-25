<?php

$app->group('/', function () {
    $this->map(['GET', 'POST'], 'daftar', 'Auth:register')->setName('register');
    $this->map(['GET', 'POST'], 'daftar/pemerintah', 'Auth:registerPemerintah')->setName('registerPemerintah');
    $this->map(['GET', 'POST'], 'daftar/bkk', 'Auth:registerBkk')->setName('registerBkk');
    $this->map(['GET', 'POST'], 'daftar/perguruan-tinggi', 'Auth:registerPerguruanTinggi')->setName('registerPerguruanTinggi');
    $this->map(['GET', 'POST'], 'daftar/pelatihan', 'Auth:registerLpk')->setName('registerLpk');
    $this->map(['GET', 'POST'], 'daftar/akademi', 'Auth:registerAkademi')->setName('registerAkademi');
    $this->map(['GET', 'POST'], 'daftar_perusahaan', 'Auth:registerPerusahaan')->setName('registerEmployer');
    $this->map(['GET', 'POST'], 'daftar_pencarikerja', 'Auth:registerJobseeker')->setName('registerjobseeker');
    $this->map(['GET', 'POST'], 'forgot-password', 'Auth:forgotPassword')->setName('forgot-password');
    $this->map(['GET', 'POST'], 'reset-password', 'Auth:resetPassword')->setName('reset-password');
    $this->map(['GET', 'POST'], 'activate', 'Auth:activate')->setName('activate');
    $this->map(['GET'], 'daftar-pencaker-berhasil', 'Auth:daftarberhasil')
        ->setName('daftar-pencaker-berhasil');
    $this->map(['GET'], 'daftar-perusahaan-berhasil', 'Auth:daftarberhasil')
    ->setName('daftar-perusahaan-berhasil');
})
->add(new App\Middleware\Guest($container))
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\Seo($container));

$app->map(['GET', 'POST'], '/login', 'Auth:login')
    ->setName('login')
    ->add(new App\Middleware\Guest($container))
    ->add($container->get('csrf'))
    ->add(new App\Middleware\Seo($container));

$app->map(['GET', 'POST'], '/login-modal', 'Auth:loginModal')
    ->setName('login-modal')
    ->add(new App\Middleware\Guest($container))
    ->add($container->get('csrf'))
    ->add(new App\Middleware\Seo($container));

$app->get('/logout', 'Auth:logout')->setName('logout');
$app->get('/logout-bkol', 'Auth:logoutbkol')->setName('logoutbkol');

$app->post('/daftar_perusahaan_api', 'Auth:registerPerusahaanAPI')->setName('registerPerusahaanAPI');
$app->post('/daftar_pencaker_api', 'Auth:registerPencakerAPI')->setName('registerPencakerAPI');
$app->post('/daftar_bkk_api', 'Auth:registerBkkApi')->setName('registerBkkApi');
$app->post('/daftar_lpk_api', 'Auth:registerLpkApi')->setName('registerLpkApi');
$app->post('/daftar_pt_api', 'Auth:registerPtApi')->setName('registerPtApi');
$app->get('/resend_activation_api/{user_id}', 'Auth:resendActivation')->setName('resendActivation');