<?php


$app->group('/lpk/dashboard', function () use ($app, $container) {
    //$app->get('', 'DashboardLpk:dashboard')
    $app->map(['GET', 'POST'],'', 'ProfileLembagaPelatihanKerja:profile')
        ->setName('lpk-dashboard');
    $app->map(['GET', 'POST'], '/profile', 'ProfileLembagaPelatihanKerja:profile')
        ->setName('profile-lpk');
    $app->map(['GET', 'POST'], '/pengaturan-akun', 'PengaturanAkun:akun')
        ->setName('lpk-dashboard-settings-akun');
        // keterampilan
    $app->group('/keterampilan', function () use ($app) {
        // Main 
        $app->get('', 'KeterampilanLpk:index')
        ->setName('lpk-dashboard-keterampilan');
        $app->group('', function () use ($app) {
            $app->map(['GET', 'POST'], '/edit[/{id}]', 'KeterampilanLpk:edit')
                ->setName('lpk-dashboard-keterampilan-edit');
            $app->map(['GET', 'POST'], '/add', 'KeterampilanLpk:add')
                ->setName('lpk-dashboard-keterampilan-add');
            $app->post('/delete', 'KeterampilanLpk:delete')
                ->setName('lpk-dashboard-keterampilan-delete');
        });
    });
    //Pelatihan
    $app->group('/pelatihan', function () use ($app) {
        $app->get('', 'DashboardPelatihan:pelatihan')
        ->setName('lpk-dashboard-pelatihan');
        $app->get('/datatables', 'DashboardPelatihan:datatables')
        ->setName('lpk-dashboard-pelatihan-datatables');
        $app->group('', function () use ($app) {
            // Unpublish Blog
            $app->post('/unpublish', 'DashboardPelatihan:pelatihanUnpublish')
                ->setName('lpk-dashboard-pelatihan-unpublish');
            // Publish Blog
            $app->post('/publish', 'DashboardPelatihan:pelatihanPublish')
                ->setName('lpk-dashboard-pelatihan-publish');
            // Edit Blog Post
            $app->map(['GET', 'POST'], '/edit/[/{post_id}]', 'DashboardPelatihan:pelatihanEdit')
                ->setName('lpk-dashboard-pelatihan-edit');
            $app->map(['GET', 'POST'], '/laporan/[/{post_id}]', 'DashboardPelatihan:pelatihanLaporan')
                ->setName('lpk-dashboard-pelatihan-laporan');
                $app->map(['GET', 'POST'], '/laporan/edit[/{laporan_id}]', 'DashboardPelatihan:editLaporan')
                    ->setName('lpk-dashboard-pelatihan-laporan-edit');
            // Add Blog Post
            $app->map(['GET', 'POST'], '/add', 'DashboardPelatihan:pelatihanAdd')
                ->setName('lpk-dashboard-pelatihan-add');
            // Delete Blog Post
            $app->post('/delete', 'DashboardPelatihan:pelatihanDelete')
                ->setName('lpk-dashboard-pelatihan-delete');
            $app->map(['GET', 'POST'], '/peserta/[/{post_id}]', 'DashboardPelatihan:PesertaPelatihan')
                ->setName('lpk-dashboard-peserta-pelatihan');

            $app->map(['GET', 'POST'], '/peserta/edit/[/{post_id}]', 'DashboardPelatihan:PesertaPelatihanEdit')
                    ->setName('lpk-dashboard-peserta-pelatihan-edit');

            $app->post('/peserta/lengkap', 'DashboardPelatihan:pelatihanSyaratLengkap')
                ->setName('lpk-dashboard-peserta-pelatihan-lengkap');
        });


    });

    // Berita
    $app->group('/berita', function () use ($app) {
        // Main Berita Admin
        $app->get('', 'FrontBeritaDashboard:index')
        ->setName('lpk-dashboard-berita');
        // Berita Actions
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontBeritaDashboard:blogUnpublish')
                ->setName('lpk-dashboard-berita-unpublish');
            // Publish 
            $app->post('/publish', 'FrontBeritaDashboard:blogPublish')
                ->setName('lpk-dashboard-berita-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontBeritaDashboard:blogEdit')
                ->setName('lpk-dashboard-berita-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontBeritaDashboard:blogAdd')
                ->setName('lpk-dashboard-berita-add');
            // Delete  Post
            $app->post('/delete', 'FrontBeritaDashboard:blogDelete')
                ->setName('lpk-dashboard-berita-delete');
        });
    });
    
    // Agenda
    $app->group('/agenda', function () use ($app) {
        // Main Agenda Admin
        $app->get('', 'FrontAgendaDashboard:agenda')
        ->setName('lpk-dashboard-agenda');
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontAgendaDashboard:agendaUnpublish')
                ->setName('lpk-dashboard-agenda-unpublish');
            // Publish 
            $app->post('/publish', 'FrontAgendaDashboard:agendaPublish')
                ->setName('lpk-dashboard-agenda-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontAgendaDashboard:agendaEdit')
                ->setName('lpk-dashboard-agenda-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontAgendaDashboard:agendaAdd')
                ->setName('lpk-dashboard-agenda-add');
            // Delete  Post
            $app->post('/delete', 'FrontAgendaDashboard:AgendaDelete')
                ->setName('lpk-dashboard-agenda-delete');
        });
    });

    // Gallery album
    // Gallery Admin
    $app->group('/gallery', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'FrontGalleryDashboard:gallery')
        ->setName('lpk-dashboard-gallery');
        $app->get('/datatables', 'FrontGalleryDashboard:datatables')
        ->setName('lpk-dashboard-gallery-datatables');
        // gallery Post Actions
        $app->group('', function () use ($app) {
            // Unpublish gallery
            $app->post('/unpublish', 'FrontGalleryDashboard:galleryUnpublish')
                ->setName('lpk-dashboard-gallery-unpublish');
            // Publish gallery
            $app->post('/publish', 'FrontGalleryDashboard:galleryPublish')
                ->setName('lpk-dashboard-gallery-publish');
            // Edit gallery Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontGalleryDashboard:galleryEdit')
                ->setName('lpk-dashboard-gallery-edit');
            // Add gallery Post
            $app->map(['GET', 'POST'], '/add', 'FrontGalleryDashboard:galleryAdd')
                ->setName('lpk-dashboard-gallery-add');
            // Delete gallery Post
            $app->post('/delete', 'FrontGalleryDashboard:galleryDelete')
                ->setName('lpk-dashboard-gallery-delete');
        });
    });
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Lpk($container))
->add($container->get('csrf'));
