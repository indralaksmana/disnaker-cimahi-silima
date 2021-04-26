<?php


$app->group('/perguruan-tinggi/dashboard', function () use ($app, $container) {
    //$app->get('', 'DashboardPerguruanTinggi:dashboard')
    $app->map(['GET', 'POST'],'', 'ProfilePerguruanTinggi:profile')
        ->setName('pt-dashboard');
    // Profile
    $app->map(['GET', 'POST'], '/profile', 'ProfilePerguruanTinggi:profile')
        ->setName('profile-perguruan-tinggi');
    $app->map(['GET', 'POST'], '/pengaturan-akun', 'PengaturanAkun:akun')
        ->setName('pt-dashboard-settings-akun');
    // Program Studi
    $app->group('/program-studi', function () use ($app) {
        // Main 
        $app->get('', 'ProgramStudiDikti:index')
        ->setName('pt-dashboard-program-studi');
        $app->group('', function () use ($app) {
            $app->map(['GET', 'POST'], '/edit[/{id}]', 'ProgramStudiDikti:edit')
                ->setName('pt-dashboard-program-studi-edit');
            $app->map(['GET', 'POST'], '/add', 'ProgramStudiDikti:add')
                ->setName('pt-dashboard-program-studi-add');
            $app->post('/delete', 'ProgramStudiDikti:delete')
                ->setName('pt-dashboard-program-studi-delete');
        });

    
        $app->map(['GET', 'POST'], '/upload[/{id}/{nama}]', 'ProgramStudiDikti:upload')
        ->setName('pt-dashboard-program-studi-upload');

        $app->post('/uploadaction', 'ProgramStudiDikti:uploadAction')
        ->setName('pt-dashboard-program-studi-uploadaction');

        $app->post('/create-pencaker', 'ProgramStudiDikti:createPencakerAlumni')
        ->setName('pt-dashboard-program-studi-createpencaker');
        
    });

    // Berita
    $app->group('/berita', function () use ($app) {
        // Main Berita Admin
        $app->get('', 'FrontBeritaDashboard:index')
        ->setName('pt-dashboard-berita');
        // Berita Actions
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontBeritaDashboard:blogUnpublish')
                ->setName('pt-dashboard-berita-unpublish');
            // Publish 
            $app->post('/publish', 'FrontBeritaDashboard:blogPublish')
                ->setName('pt-dashboard-berita-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontBeritaDashboard:blogEdit')
                ->setName('pt-dashboard-berita-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontBeritaDashboard:blogAdd')
                ->setName('pt-dashboard-berita-add');
            // Delete  Post
            $app->post('/delete', 'FrontBeritaDashboard:blogDelete')
                ->setName('pt-dashboard-berita-delete');
        });
    });
    
    // Agenda
    $app->group('/agenda', function () use ($app) {
        // Main Agenda Admin
        $app->get('', 'FrontAgendaDashboard:agenda')
        ->setName('pt-dashboard-agenda');
        $app->group('', function () use ($app) {
            // Unpublish 
            $app->post('/unpublish', 'FrontAgendaDashboard:agendaUnpublish')
                ->setName('pt-dashboard-agenda-unpublish');
            // Publish 
            $app->post('/publish', 'FrontAgendaDashboard:agendaPublish')
                ->setName('pt-dashboard-agenda-publish');
            // Edit  Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontAgendaDashboard:agendaEdit')
                ->setName('pt-dashboard-agenda-edit');
            // Add  Post
            $app->map(['GET', 'POST'], '/add', 'FrontAgendaDashboard:agendaAdd')
                ->setName('pt-dashboard-agenda-add');
            // Delete  Post
            $app->post('/delete', 'FrontAgendaDashboard:AgendaDelete')
                ->setName('pt-dashboard-agenda-delete');
        });
    });

    // Gallery album
    $app->group('/gallery', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'FrontGalleryDashboard:gallery')
        ->setName('pt-dashboard-gallery');
        $app->get('/datatables', 'FrontGalleryDashboard:datatables')
        ->setName('pt-dashboard-gallery-datatables');
        // gallery Post Actions
        $app->group('', function () use ($app) {
            // Unpublish gallery
            $app->post('/unpublish', 'FrontGalleryDashboard:galleryUnpublish')
                ->setName('pt-dashboard-gallery-unpublish');
            // Publish gallery
            $app->post('/publish', 'FrontGalleryDashboard:galleryPublish')
                ->setName('pt-dashboard-gallery-publish');
            // Edit gallery Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'FrontGalleryDashboard:galleryEdit')
                ->setName('pt-dashboard-gallery-edit');
            // Add gallery Post
            $app->map(['GET', 'POST'], '/add', 'FrontGalleryDashboard:galleryAdd')
                ->setName('pt-dashboard-gallery-add');
            // Delete gallery Post
            $app->post('/delete', 'FrontGalleryDashboard:galleryDelete')
                ->setName('pt-dashboard-gallery-delete');
        });
    });
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\PerguruanTinggi($container))
->add($container->get('csrf'));
