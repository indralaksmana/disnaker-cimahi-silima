<?php

$app->group('/dashboard/perusahaan', function () use ($app, $container) {
    $app->group('', function () use ($app) {
        // View Comments
        $app->get('', 'DashboardPerusahaan:dashboardperusahaan')
            ->setName('dashboardperusahaan');
        $app->map(['GET', 'POST'], '/pengaturan-akun', 'DashboardPerusahaan:SettingsAkun')
            ->setName('dashboardperusahaan-settings-akun');
        $app->map(['GET', 'POST'], '/pengaturan-akun/update', 'DashboardPerusahaan:PengaturanAkun')
            ->setName('dashboardperusahaan-settings-akun-update');

        $app->map(['GET', 'POST'], '/profil-perusahaan', 'ProfilePerusahaan:profile')
            ->setName('dashboardperusahaan-profile');

        $app->map(['GET', 'POST'], '/produk-jasa', 'ProdukJasaPerusahaan:produkjasa')
            ->setName('dashboardperusahaan-produk-jasa');
        $app->map(['GET', 'POST'], '/profil-perusahaan/update', 'DashboardPerusahaan:ProfilPerusahaan')
            ->setName('dashboardperusahaan-profile-update');
        $app->map(['GET', 'POST'], '/upload-image', 'DashboardPerusahaan:uploadImage')
            ->setName('dashboardperusahaan-upload-image');
        $app->map(['GET', 'POST'], '/settings', 'Resume:mySettingsAccount')
            ->setName('update-pengaturan-settings');
    });
    $app->group('/shortlist', function () use ($app) {
      $app->map(['GET', 'POST'],'', 'Shortlist:index')
          ->setName('dashboardperusahaan-shortlist');
      $app->post('/delete', 'Shortlist:delete_favorite')
          ->setName('dashboardperusahaan-delete');
    });

    $app->group('/lowongan', function () use ($app) {
        // Main Lowongan Admin
        $app->get('', 'DashboardLowongan:job')
        ->setName('dashboardperusahaan-lowongan');
        $app->get('/datatables', 'AdminJob:datatables')
        ->setName('dashboardperusahaan-lowongan-datatables');
        // Lowongan Post Actions
        $app->group('', function () use ($app) {
            // Unpublish Lowongan
            $app->post('/unpublish', 'DashboardLowongan:jobUnpublish')
                ->setName('dashboardperusahaan-lowongan-unpublish');
            // Publish Lowongan
            $app->post('/publish', 'DashboardLowongan:jobPublish')
                ->setName('dashboardperusahaan-lowongan-publish');
            // Edit Lowongan Post
            $app->map(['GET', 'POST'], '/edit[/{id}]', 'DashboardLowongan:jobEdit')
                ->setName('dashboardperusahaan-lowongan-edit');
            // Add Lowongan Post
            $app->map(['GET', 'POST'], '/add', 'DashboardLowongan:jobAdd')
                ->setName('dashboardperusahaan-lowongan-add');
            // Delete Lowongan Post
            $app->post('/delete', 'DashboardLowongan:jobDelete')
                ->setName('dashboardperusahaan-lowongan-delete');
        });
        // Lowongan Comments
        $app->group('/pelamar', function () use ($app) {
            $app->get('', 'DashboardPelamarLowongan:apply')
                ->setName('dashboardperusahaan-pelamar');
            // Proses 1
            $app->map(['GET', 'POST'], '/review/{username}/{id}', 'DashboardPelamarLowongan:Review')
            ->setName('dashboardperusahaan-pelamar-review');
            $app->map(['GET', 'POST'], '/detail-lamaran/{username}/{id}', 'DashboardPelamarLowongan:DetailLamaran')
                ->setName('dashboardperusahaan-pelamar-details');
            // Prosses 2
            $app->post('/jadikan-kandidate', 'DashboardPelamarLowongan:JadikanKandidat')
                ->setName('dashboardperusahaan-pelamar-kandidate');
            // Prorsse 3
            $app->map(['GET', 'POST'], '/undang-inteview/{username}/{id}', 'DashboardPelamarLowongan:UndangInterview')
                ->setName('dashboardperusahaan-pelamar-undanginterview');

            // Prorsse 4
            $app->post('/sudah-interview', 'DashboardPelamarLowongan:SudahInterview')
                ->setName('dashboardperusahaan-pelamar-sudah-interview');

            // Di Terima Bekerja
            $app->map(['GET', 'POST'], '/terima-bekerja/{username}/{id}', 'DashboardPelamarLowongan:TerimaBekerja')
                ->setName('dashboardperusahaan-pelamar-terima-bekerja');

            $app->post('/berhenti-bekerja', 'DashboardPelamarLowongan:BerhentiBekerja')
                ->setName('dashboardperusahaan-pelamar-berhenti-bekerja');

            $app->post('/delete', 'DashboardPelamarLowongan:HapusLamaran')
                ->setName('dashboardperusahaan-pelamar-delete');
        });
    })
    ->add(new App\Middleware\BkolProfileCheck($container));

    $app->get('/ganti-password', 'Admin:GantiPassword')
        ->setName('dashboardperusahaan-ganti-password');
    $app->group('/resume', function () use ($app) {
        // View Comments
        $app->map(['GET', 'POST'], '', 'Resume:myAccount')
        ->setName('my-account');
        $app->map(['GET', 'POST'], '/resume-upload', 'Resume:resumeUpload')
        ->setName('resume-upload');

        $app->group('/pendidikan', function () use ($app) {
            $app->get('', 'Pendidikan:pendidikan')
            ->setName('resume-pendidikan');
            $app->group('', function () use ($app) {
                // Edit
                $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'Pendidikan:pendidikanEdit')
                    ->setName('resume-pendidikan-edit');
                // Tambah
                $app->map(['GET', 'POST'], '/add', 'Pendidikan:pendidikanAdd')
                    ->setName('resume-pendidikan-add');
                // Hapus
                $app->post('/delete', 'Pendidikan:pendidikanDelete')
                    ->setName('resume-pendidikan-delete');
            });
        });
        $app->group('/pendidikannoformal', function () use ($app) {
            // List Resume PendidikanNonFormal
            $app->get('', 'PendidikanNonFormal:nonformalpendidikan')
            ->setName('resume-nonformalpendidikan');
            // Post Action
            $app->group('', function () use ($app) {
                // Edit Resume PendidikanNonFormal
                $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'PendidikanNonFormal:nonformalpendidikanEdit')
                    ->setName('resume-nonformalpendidikan-edit');
                // Add Resume PendidikanNonFormal
                $app->map(['GET', 'POST'], '/add', 'PendidikanNonFormal:nonformalpendidikanAdd')
                    ->setName('resume-nonformalpendidikan-add');
                // Hapus PendidikanNonFormal
                $app->post('/delete', 'PendidikanNonFormal:nonformalpendidikanDelete')
                    ->setName('resume-nonformalpendidikan-delete');
            });
        });

        $app->group('/pekerjaan', function () use ($app) {
            // Main Lowongan Admin
            $app->get('', 'Pekerjaan:pekerjaan')
            ->setName('resume-pekerjaan');

            $app->get('/datatables', 'Pekerjaan:datatables')
            ->setName('resume-pekerjaan-datatables');

            // gallery Post Actions
            $app->group('', function () use ($app) {
                // Unpublish gallery
                $app->post('/unpublish', 'Pekerjaan:pekerjaanUnpublish')
                    ->setName('resume-pekerjaan-unpublish');
                // Publish gallery
                $app->post('/publish', 'Pekerjaan:pekerjaanPublish')
                    ->setName('resume-pekerjaan-publish');
                // Edit gallery Post
                $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'Pekerjaan:pekerjaanEdit')
                    ->setName('resume-pekerjaan-edit');
                // Add gallery Post
                $app->map(['GET', 'POST'], '/add', 'Pekerjaan:pekerjaanAdd')
                    ->setName('resume-pekerjaan-add');
                // Delete gallery Post
                $app->post('/delete', 'Pekerjaan:pekerjaanDelete')
                    ->setName('resume-pekerjaan-delete');
            });
        });
    })->add(new App\Middleware\ResumeCheck($container));

    $app->group('/lamaranku', function () use ($app) {
        // View Comments
        $app->get('', 'AdminProposal:proposal')
            ->setName('admin-job-proposal');
        // Datatables
        $app->get('/datatables', 'AdminProposal:datatables')
            ->setName('admin-job-proposal-datatables');
        $app->get('/{comment_id}', 'AdminProposal:proposalDetails')
            ->setName('admin-job-proposal-details');
        // Unpublish Comment
        $app->post('/unpublish', 'AdminProposal:proposalUnpublish')
            ->setName('admin-job-proposal-unpublish');
        // Publish Comment
        $app->post('/publish', 'AdminProposal:proposalPublish')
            ->setName('admin-job-proposal-publish');
        // Delte Comment
        $app->post('/delete', 'AdminProposal:proposalDelete')
            ->setName('admin-job-proposal-delete');
    });
    $app->get('/jobseeker/{username}', 'Resume:CVJobseeker')
            ->setName('dashboardperusahaan-pelamar-author');
    $app->get('/jobseeker', 'BkolJobseeker:JobseekerList')
            ->setName('bkol-jobseeker');

            // Berita
    $app->group('/berita', function () use ($app) {
        // Main Berita Admin
        $app->get('', 'FrontBeritaDashboard:index')
        ->setName('perusahaan-dashboard-berita');
        // Berita Actions
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontBeritaDashboard:blogUnpublish')
                ->setName('perusahaan-dashboard-berita-unpublish');
            // Publish 
            $app->post('/publish', 'FrontBeritaDashboard:blogPublish')
                ->setName('perusahaan-dashboard-berita-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontBeritaDashboard:blogEdit')
                ->setName('perusahaan-dashboard-berita-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontBeritaDashboard:blogAdd')
                ->setName('perusahaan-dashboard-berita-add');
            // Delete  Post
            $app->post('/delete', 'FrontBeritaDashboard:blogDelete')
                ->setName('perusahaan-dashboard-berita-delete');
        });
    });
    
    // Agenda
    $app->group('/agenda', function () use ($app) {
        // Main Agenda Admin
        $app->get('', 'FrontAgendaDashboard:agenda')
        ->setName('perusahaan-dashboard-agenda');
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontAgendaDashboard:agendaUnpublish')
                ->setName('perusahaan-dashboard-agenda-unpublish');
            // Publish 
            $app->post('/publish', 'FrontAgendaDashboard:agendaPublish')
                ->setName('perusahaan-dashboard-agenda-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontAgendaDashboard:agendaEdit')
                ->setName('perusahaan-dashboard-agenda-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontAgendaDashboard:agendaAdd')
                ->setName('perusahaan-dashboard-agenda-add');
            // Delete  Post
            $app->post('/delete', 'FrontAgendaDashboard:AgendaDelete')
                ->setName('perusahaan-dashboard-agenda-delete');
        });
    });

    // Gallery album
    // Gallery Admin
    $app->group('/gallery', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'FrontGalleryDashboard:gallery')
        ->setName('perusahaan-dashboard-gallery');
        $app->get('/datatables', 'FrontGalleryDashboard:datatables')
        ->setName('perusahaan-dashboard-gallery-datatables');
        // gallery Post Actions
        $app->group('', function () use ($app) {
            // Unpublish gallery
            $app->post('/unpublish', 'FrontGalleryDashboard:galleryUnpublish')
                ->setName('perusahaan-dashboard-gallery-unpublish');
            // Publish gallery
            $app->post('/publish', 'FrontGalleryDashboard:galleryPublish')
                ->setName('perusahaan-dashboard-gallery-publish');
            // Edit gallery Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontGalleryDashboard:galleryEdit')
                ->setName('perusahaan-dashboard-gallery-edit');
            // Add gallery Post
            $app->map(['GET', 'POST'], '/add', 'FrontGalleryDashboard:galleryAdd')
                ->setName('perusahaan-dashboard-gallery-add');
            // Delete gallery Post
            $app->post('/delete', 'FrontGalleryDashboard:galleryDelete')
                ->setName('perusahaan-dashboard-gallery-delete');
        });
    });
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));
