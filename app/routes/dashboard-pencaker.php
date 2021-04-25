<?php

$app->group('/dashboard/pencarikerja', function () use ($app, $container) {
    $app->group('', function () use ($app) {
        $app->get('', 'DashboardPencaker:dashboardpencaker')
            ->setName('dashboardpencaker');
        $app->map(['GET', 'POST'], '/pengaturan-akun', 'PengaturanAkun:akun')
            ->setName('dashboardpencaker-settings');
    });
    $app->group('/resume', function () use ($app) {
        // View Comments
        $app->map(['GET', 'POST'], '/biodata', 'DashboardPencakerResume:Biodata')
        ->setName('dashboardpencaker-biodata');
        $app->map(['GET', 'POST'], '/resume-upload', 'DashboardPencakerResume:resumeUpload')
        ->setName('resume-upload');
        $app->post('/resume-upload/delete', 'DashboardPencakerResume:resumeUploadDelete')
            ->setName('resume-upload-delete');
        $app->map(['GET', 'POST'], '/edit[/{dokumen_id}]', 'DashboardPencakerResume:resumeUploadEdit')
            ->setName('resume-upload-edit');
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
            // Main Blog Admin
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

        $app->group('/kompetensi', function () use ($app) {
            // Main Blog Admin
            $app->get('', 'Kompetensi:kompetensi')
            ->setName('resume-kompetensi');

            $app->get('/datatables', 'Kompetensi:datatables')
            ->setName('resume-kompetensi-datatables');

            // gallery Post Actions
            $app->group('', function () use ($app) {
                // Unpublish gallery
                $app->post('/unpublish', 'Kompetensi:unpublish')
                    ->setName('resume-kompetensi-unpublish');
                // Publish gallery
                $app->post('/publish', 'Kompetensi:publish')
                    ->setName('resume-kompetensi-publish');
                // Edit gallery Post
                $app->map(['GET', 'POST'], '/edit[/{id}]', 'Kompetensi:edit')
                    ->setName('resume-kompetensi-edit');
                // Add gallery Post
                $app->map(['GET', 'POST'], '/add', 'Kompetensi:add')
                    ->setName('resume-kompetensi-add');
                // Delete gallery Post
                $app->post('/delete', 'Kompetensi:delete')
                    ->setName('resume-kompetensi-delete');
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
            ->setName('admin-job-apply-author');

    $app->get('/jobseeker', 'BkolJobseeker:JobseekerList')
            ->setName('bkol-jobseeker');

})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));
