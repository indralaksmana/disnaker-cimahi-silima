<?php

$app->group('/dashboard/pemerintah', function () use ($app, $container) {
    $app->group('', function () use ($app) {
        // View Comments
        //$app->get('', 'DashboardPemerintah:dashboard')
        $app->map(['GET', 'POST'],'', 'ProfilePemerintah:profile')
            ->setName('dashboardpemerintah');
        $app->map(['GET', 'POST'], '/pengaturan-akun', 'DashboardPemerintah:SettingsAkun')
            ->setName('dashboardpemerintah-settings-akun');
        $app->map(['GET', 'POST'], '/pengaturan-akun/update', 'DashboardPemerintah:PengaturanAkun')
            ->setName('dashboardpemerintah-settings-akun-update');
        $app->map(['GET', 'POST'], '/profil-pemerintah', 'ProfilePemerintah:profile')
            ->setName('dashboardpemerintah-profile');
        $app->map(['GET', 'POST'], '/upload-image', 'DashboardPemerintah:uploadImage')
            ->setName('dashboardpemerintah-upload-image');
    
    });
    $app->group('/lowongan', function () use ($app) {
        // Main Lowongan Admin
        $app->get('', 'DashboardLowongan:job')
        ->setName('dashboardpemerintah-lowongan');
        $app->get('/datatables', 'AdminJob:datatables')
        ->setName('dashboardpemerintah-lowongan-datatables');
        // Lowongan Post Actions
        $app->group('', function () use ($app) {
            // Unpublish Lowongan
            $app->post('/unpublish', 'DashboardLowongan:jobUnpublish')
                ->setName('dashboardpemerintah-lowongan-unpublish');
            // Publish Lowongan
            $app->post('/publish', 'DashboardLowongan:jobPublish')
                ->setName('dashboardpemerintah-lowongan-publish');
            // Edit Lowongan Post
            $app->map(['GET', 'POST'], '/edit[/{id}]', 'DashboardLowongan:jobEdit')
                ->setName('dashboardpemerintah-lowongan-edit');
            // Add Lowongan Post
            $app->map(['GET', 'POST'], '/add', 'DashboardLowongan:jobAdd')
                ->setName('dashboardpemerintah-lowongan-add');
            // Delete Lowongan Post
            $app->post('/delete', 'DashboardLowongan:jobDelete')
                ->setName('dashboardpemerintah-lowongan-delete');
        });

        // Lowongan Comments
        $app->group('/pelamar', function () use ($app) {
            $app->get('', 'DashboardPelamarLowonganPemerintah:apply')
                ->setName('dashboardpemerintah-pelamar');
            // Proses 1
            $app->map(['GET', 'POST'], '/review/{username}/{id}', 'DashboardPelamarLowonganPemerintah:Review')
            ->setName('dashboardpemerintah-pelamar-review');
            $app->map(['GET', 'POST'], '/detail-lamaran/{username}/{id}', 'DashboardPelamarLowonganPemerintah:DetailLamaran')
                ->setName('dashboardpemerintah-pelamar-details');
            // Prosses 2
            $app->post('/jadikan-kandidate', 'DashboardPelamarLowonganPemerintah:JadikanKandidat')
                ->setName('dashboardpemerintah-pelamar-kandidate');
            // Prorsse 3
            $app->map(['GET', 'POST'], '/undang-inteview/{username}/{id}', 'DashboardPelamarLowonganPemerintah:UndangInterview')
                ->setName('dashboardpemerintah-pelamar-undanginterview');

            // Prorsse 4
            $app->post('/sudah-interview', 'DashboardPelamarLowonganPemerintah:SudahInterview')
                ->setName('dashboardpemerintah-pelamar-sudah-interview');

            // Di Terima Bekerja
            $app->map(['GET', 'POST'], '/terima-bekerja/{username}/{id}', 'DashboardPelamarLowonganPemerintah:TerimaBekerja')
                ->setName('dashboardpemerintah-pelamar-terima-bekerja');

            $app->post('/berhenti-bekerja', 'DashboardPelamarLowonganPemerintah:BerhentiBekerja')
                ->setName('dashboardpemerintah-pelamar-berhenti-bekerja');

            $app->post('/delete', 'DashboardPelamarLowonganPemerintah:HapusLamaran')
                ->setName('dashboardpemerintah-pelamar-delete');
        });

    });
    $app->group('/pelatihan', function () use ($app) {
        $app->get('', 'DashboardPelatihanPemerintah:pelatihan')
        ->setName('dashboardpemerintah-pelatihan');
        $app->get('/datatables', 'DashboardPelatihanPemerintah:datatables')
        ->setName('dashboardpemerintah-pelatihan-datatables');
        $app->group('', function () use ($app) {
            // Unpublish Blog
            $app->post('/unpublish', 'DashboardPelatihanPemerintah:pelatihanUnpublish')
                ->setName('dashboardpemerintah-pelatihan-unpublish');
            // Publish Blog
            $app->post('/publish', 'DashboardPelatihanPemerintah:pelatihanPublish')
                ->setName('dashboardpemerintah-pelatihan-publish');
            // Edit Blog Post
            $app->map(['GET', 'POST'], '/edit/[/{post_id}]', 'DashboardPelatihanPemerintah:pelatihanEdit')
                ->setName('dashboardpemerintah-pelatihan-edit');
            $app->map(['GET', 'POST'], '/laporan/[/{post_id}]', 'DashboardPelatihanPemerintah:pelatihanLaporan')
                ->setName('dashboardpemerintah-pelatihan-laporan');
                $app->map(['GET', 'POST'], '/laporan/edit[/{laporan_id}]', 'DashboardPelatihanPemerintah:editLaporan')
                    ->setName('dashboardpemerintah-pelatihan-laporan-edit');
            // Add Blog Post
            $app->map(['GET', 'POST'], '/add', 'DashboardPelatihanPemerintah:pelatihanAdd')
                ->setName('dashboardpemerintah-pelatihan-add');
            // Delete Blog Post
            $app->post('/delete', 'DashboardPDashboardPelatihanPemerintahelatihan:pelatihanDelete')
                ->setName('dashboardpemerintah-pelatihan-delete');
            $app->map(['GET', 'POST'], '/peserta/[/{post_id}]', 'DashboardPelatihanPemerintah:PesertaPelatihan')
                ->setName('dashboardpemerintah-peserta-pelatihan');

            $app->map(['GET', 'POST'], '/peserta/edit/[/{post_id}]', 'DashboardPelatihan:PesertaPelatihanEdit')
                    ->setName('dashboardpemerintah-peserta-pelatihan-edit');

            $app->post('/peserta/lengkap', 'DashboardPelatihanPemerintah:pelatihanSyaratLengkap')
                ->setName('dashboardpemerintah-peserta-pelatihan-lengkap');
        });


    });
})
->add(new App\Middleware\Auth($container))
->add($container->get('csrf'));

