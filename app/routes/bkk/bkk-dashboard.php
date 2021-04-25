<?php


$app->group('/bkk/dashboard', function () use ($app, $container) {
    //$app->get('', 'DashboardBkk:dashboard')
    $app->map(['GET', 'POST'],'', 'ProfileBursaKerjaKhusus:profile')
        ->setName('bkk-dashboard');

    // Jurusan
    $app->group('/jurusan', function () use ($app) {
        // Main 
        $app->get('', 'JurusanBkk:index')
        ->setName('bkk-dashboard-jurusan');
        $app->group('', function () use ($app) {
            $app->map(['GET', 'POST'], '/edit[/{id}]', 'JurusanBkk:edit')
                ->setName('bkk-dashboard-jurusan-edit');
            $app->map(['GET', 'POST'], '/add', 'JurusanBkk:add')
                ->setName('bkk-dashboard-jurusan-add');
            $app->post('/delete', 'JurusanBkk:delete')
                ->setName('bkk-dashboard-jurusan-delete');
            
                $app->map(['GET', 'POST'], '/upload[/{id}/{nama}]', 'JurusanBkk:upload')
                ->setName('bkk-dashboard-jurusan-upload');

                $app->post('/uploadaction', 'JurusanBkk:uploadAction')
                ->setName('bkk-dashboard-jurusan-uploadaction');

                $app->get('/detail-alumni[/{username}]', 'JurusanBkk:detailAlumni')
                ->setName('bkk-dashboard-detail-alumni');

                $app->post('/create-pencaker', 'JurusanBkk:createPencakerAlumni')
                ->setName('bkk-dashboard-jurusan-createpencaker');
        });
    });

    // Profile
    $app->map(['GET', 'POST'], '/profile', 'ProfileBursaKerjaKhusus:profile')
        ->setName('profile-bkk');

    $app->map(['GET', 'POST'], '/pengaturan-akun', 'PengaturanAkun:akun')
        ->setName('bkk-dashboard-settings-akun');

    // Berita
    $app->group('/berita', function () use ($app) {
        // Main Berita Admin
        $app->get('', 'FrontBeritaDashboard:index')
        ->setName('bkk-dashboard-berita');
        // Berita Actions
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontBeritaDashboard:blogUnpublish')
                ->setName('bkk-dashboard-berita-unpublish');
            // Publish 
            $app->post('/publish', 'FrontBeritaDashboard:blogPublish')
                ->setName('bkk-dashboard-berita-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontBeritaDashboard:blogEdit')
                ->setName('bkk-dashboard-berita-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontBeritaDashboard:blogAdd')
                ->setName('bkk-dashboard-berita-add');
            // Delete  Post
            $app->post('/delete', 'FrontBeritaDashboard:blogDelete')
                ->setName('bkk-dashboard-berita-delete');
        });
    });
    
    // Agenda
    $app->group('/agenda', function () use ($app) {
        // Main Agenda Admin
        $app->get('', 'FrontAgendaDashboard:agenda')
        ->setName('bkk-dashboard-agenda');
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontAgendaDashboard:agendaUnpublish')
                ->setName('bkk-dashboard-agenda-unpublish');
            // Publish 
            $app->post('/publish', 'FrontAgendaDashboard:agendaPublish')
                ->setName('bkk-dashboard-agenda-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontAgendaDashboard:agendaEdit')
                ->setName('bkk-dashboard-agenda-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontAgendaDashboard:agendaAdd')
                ->setName('bkk-dashboard-agenda-add');
            // Delete  Post
            $app->post('/delete', 'FrontAgendaDashboard:AgendaDelete')
                ->setName('bkk-dashboard-agenda-delete');
        });
    });

    // Gallery album
    // Gallery Admin
    $app->group('/gallery', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'FrontGalleryDashboard:gallery')
        ->setName('bkk-dashboard-gallery');
        $app->get('/datatables', 'FrontGalleryDashboard:datatables')
        ->setName('bkk-dashboard-gallery-datatables');
        // gallery Post Actions
        $app->group('', function () use ($app) {
            // Unpublish gallery
            $app->post('/unpublish', 'FrontGalleryDashboard:galleryUnpublish')
                ->setName('bkk-dashboard-gallery-unpublish');
            // Publish gallery
            $app->post('/publish', 'FrontGalleryDashboard:galleryPublish')
                ->setName('bkk-dashboard-gallery-publish');
            // Edit gallery Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontGalleryDashboard:galleryEdit')
                ->setName('bkk-dashboard-gallery-edit');
            // Add gallery Post
            $app->map(['GET', 'POST'], '/add', 'FrontGalleryDashboard:galleryAdd')
                ->setName('bkk-dashboard-gallery-add');
            // Delete gallery Post
            $app->post('/delete', 'FrontGalleryDashboard:galleryDelete')
                ->setName('bkk-dashboard-gallery-delete');
        });
    });
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Bkk($container))
->add($container->get('csrf'));
