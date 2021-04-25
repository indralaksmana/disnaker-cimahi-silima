<?php


$app->group('/dashboard', function () use ($app, $container) {
    // Berita Admin
    $app->group('/berita', function () use ($app) {
        // Main Berita Admin
        $app->get('', 'AdminBlog:blog')
        ->setName('admin-blog');
        $app->get('/datatables', 'AdminBlog:datatables')
        ->setName('admin-blog-datatables');
        // Berita Actions
        $app->group('', function () use ($app) {
            // Unpublish Blog
            $app->post('/unpublish', 'AdminBlog:blogUnpublish')
                ->setName('admin-blog-unpublish');
            // Publish Blog
            $app->post('/publish', 'AdminBlog:blogPublish')
                ->setName('admin-blog-publish');
            // Edit Blog Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'AdminBlog:blogEdit')
                ->setName('admin-blog-edit');
            // Add Blog Post
            $app->map(['GET', 'POST'], '/add', 'AdminBlog:blogAdd')
                ->setName('admin-blog-add');
            // Delete Blog Post
            $app->post('/delete', 'AdminBlog:blogDelete')
                ->setName('admin-blog-delete');
        });
        // Berita Categories Actions
        $app->group('/kategori', function () use ($app) {

          $app->map(['GET', 'POST'], '', 'AdminBlogCategories:categoriesAdd')
              ->setName('admin-blog-categories-add');
            // Delete Category
            $app->post('/delete', 'AdminBlogCategories:categoriesDelete')
                ->setName('admin-blog-categories-delete');
            // Edit Category
            $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminBlogCategories:categoriesEdit')
                ->setName('admin-blog-categories-edit');
            // Add Category

        });
        // Berita Tag Actions
        $app->group('/tags', function () use ($app) {

            $app->map(['GET', 'POST'], '', 'AdminBlogTags:tagsAdd')
              ->setName('admin-blog-tags-add');

            $app->post('/delete', 'AdminBlogTags:tagsDelete')
                ->setName('admin-blog-tags-delete');
            // Edit Tag
            $app->map(['GET', 'POST'], '/edit[/{tag_id}]', 'AdminBlogTags:tagsEdit')
                ->setName('admin-blog-tags-edit');
            // Delete Tag

        });
        // Preview Berita Post
        $app->get('/preview[/{slug}]', 'AdminBlog:blogPreview')
            ->setName('admin-blog-preview');
    })
    ->add(new App\Middleware\BlogCheck($container));
    // Dashboard Home
    $app->get('', 'Admin:dashboard')
        ->setName('dashboard');
    $app->group('/shortlist', function () use ($app) {
      $app->map(['GET', 'POST'],'', 'Shortlist:index')
          ->setName('shortlist-admin');
      $app->post('/delete', 'Shortlist:delete_favorite')
          ->setName('shortlist-admin-delete');
    });
    $app->get('/ganti-password', 'Admin:GantiPassword')
        ->setName('ganti-password');
    $app->group('/bkol/pencarikerja', function () use ($app) {
        $app->get('', 'UserBkolPencariKerja:users')
            ->setName('bkol-pencarikerja');
        $app->map(['GET', 'POST'], '/add', 'UserBkolPencariKerja:usersAdd')
            ->setName('bkol-pencarikerja-add');
            
        // Delete
        $app->post('/delete', 'UserBkolPencariKerja:usersDelete')
            ->setName('bkol-pencarikerja-delete');
        // Aktipkan akun
        $app->post('/aktipkan', 'UserBkolPencariKerja:Aktipkan')
        ->setName('bkol-pencarikerja-aktipkan');
        // Non Aktipkan akun
        $app->post('/nonaktipkan', 'UserBkolPencariKerja:NonAktipkan')
        ->setName('bkol-pencarikerja-nonaktipkan');

        // Edit
        $app->map(['GET', 'POST'], '/edit/{user_id}', 'UserBkolPencariKerja:usersEdit')
            ->setName('bkol-pencarikerja-edit');

        $app->map(['GET', 'POST'], '/peserta/edit/{user_id}', 'UserBkolPencariKerja:usersEditPeserta')
            ->setName('bkol-pencarikerja-edit-peserta');
        // Detail
        // $app->get('/detail[/{bkol-pencarikerja}]', 'UserBkolPencariKerja:DetailBkolPencariKerja')
        // ->setName('detail-bkol-pencarikerja');

    });

    $app->group('/media', function () use ($app) {
        // Media
        $app->map(['GET'], '', 'AdminMedia:media')
            ->setName('admin-media');
        // Media
        $app->map(['POST'], '/folder', 'AdminMedia:mediaFolder')
            ->setName('admin-media-folder');

        $app->map(['POST'], '/folder/new', 'AdminMedia:mediaFolderNew')
            ->setName('admin-media-folder-new');

        $app->map(['POST'], '/upload', 'AdminMedia:mediaUpload')
            ->setName('admin-media-upload');

        $app->map(['POST'], '/delete', 'AdminMedia:mediaDelete')
            ->setName('admin-media-delete');

        $app->map(['GET'], '/cloudinary-sign', 'AdminMedia:cloudinarySign')
            ->setName('cloudinary-sign');
    });

    // Gallery Admin
    $app->group('/gallery', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'AdminGallery:gallery')
        ->setName('admin-gallery');

        $app->get('/datatables', 'AdminGallery:datatables')
        ->setName('admin-gallery-datatables');

        // gallery Post Actions
        $app->group('', function () use ($app) {
            // Unpublish gallery
            $app->post('/unpublish', 'AdminGallery:galleryUnpublish')
                ->setName('admin-gallery-unpublish');
            // Publish gallery
            $app->post('/publish', 'AdminGallery:galleryPublish')
                ->setName('admin-gallery-publish');
            // Edit gallery Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'AdminGallery:galleryEdit')
                ->setName('admin-gallery-edit');
            // Add gallery Post
            $app->map(['GET', 'POST'], '/add', 'AdminGallery:galleryAdd')
                ->setName('admin-gallery-add');
            // Delete gallery Post
            $app->post('/delete', 'AdminGallery:galleryDelete')
                ->setName('admin-gallery-delete');
        });
        // Gallery Categories Actions
        $app->group('/categories', function () use ($app) {
            // Delete Category
            $app->post('/delete', 'AdminGalleryCategories:categoriesDelete')
                ->setName('admin-gallery-categories-delete');
            // Edit Category
            $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminGalleryCategories:categoriesEdit')
                ->setName('admin-gallery-categories-edit');
            // Add Category
            $app->post('/add', 'AdminGalleryCategories:categoriesAdd')
                ->setName('admin-gallery-categories-add');
        });
    })
    ->add(new App\Middleware\GalleryCheck($container));

    // Agenda Admin
    $app->group('/agenda', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'AdminAgenda:agenda')
        ->setName('admin-agenda');

        $app->get('/datatables', 'AdminAgenda:datatables')
        ->setName('admin-agenda-datatables');

        // Blog Post Actions
        $app->group('', function () use ($app) {
            // Unpublish Blog
            $app->post('/unpublish', 'AdminAgenda:agendaUnpublish')
                ->setName('admin-agenda-unpublish');
            // Publish Blog
            $app->post('/publish', 'AdminAgenda:agendaPublish')
                ->setName('admin-agenda-publish');
            // Edit Blog Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'AdminAgenda:agendaEdit')
                ->setName('admin-job-edit');
            // Add Blog Post
            $app->map(['GET', 'POST'], '/add', 'AdminAgenda:agendaAdd')
                ->setName('admin-agenda-add');
            // Delete Blog Post
            $app->post('/delete', 'AdminAgenda:AgendaDelete')
                ->setName('admin-agenda-delete');
        });

        // Berita Categories Actions
        $app->group('/kategori', function () use ($app) {

            $app->map(['GET', 'POST'], '', 'AdminAgendaCategories:categoriesAdd')
                ->setName('admin-agenda-categories-add');

            // Delete Category
            $app->post('/delete', 'AdminAgendaCategories:categoriesDelete')
                ->setName('admin-agenda-categories-delete');
            // Edit Category
            $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminAgendaCategories:categoriesEdit')
                ->setName('admin-agenda-categories-edit');
            
            /*// Delete Category
            $app->post('/delete', 'AdminBlogCategories:categoriesDelete')
                ->setName('admin-blog-categories-delete');
            // Edit Category
            $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminBlogCategories:categoriesEdit')
                ->setName('admin-blog-categories-edit');
            // Add Category*/

        });

        // Blog Categories Actions
        $app->group('/categories', function () use ($app) {

            $app->map(['GET', 'POST'], '', 'AgendaCategories:categoriesAdd')
              ->setName('admin-agenda-categories-add');

            // Delete Category
            /*$app->post('/delete', 'AdminJobCategories:categoriesDelete')
                ->setName('admin-job-categories-delete');*/
            
            // Edit Category
            /*$app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminJobCategories:categoriesEdit')
                ->setName('admin-job-categories-edit');
            // Add Category
            $app->post('/add', 'AdminJobCategories:categoriesAdd')
                ->setName('admin-job-categories-add');*/
        });
    })
    ->add(new App\Middleware\AgendaCheck($container));

    // SEO Manager
    // $app->group('/seo', function () use ($app) {
    //     $app->map(['GET'], '', 'AdminSeo:seo')
    //         ->setName('admin-seo');

    //     $app->map(['GET','POST'], '/add', 'AdminSeo:seoAdd')
    //         ->setName('admin-seo-add');

    //     $app->map(['GET','POST'], '/edit/{seo_id}', 'AdminSeo:seoEdit')
    //         ->setName('admin-seo-edit');

    //     $app->map(['POST'], '/delete', 'AdminSeo:seoDelete')
    //         ->setName('admin-seo-delete');

    //     $app->map(['POST'], '/default', 'AdminSeo:seoDefault')
    //         ->setName('admin-seo-default');
    // });

    // Oauth Manager
   
    // Contact Requests
    $app->map(['GET'], '/contact', 'Admin:contact')
        ->setName('admin-contact');

    // Contact Requests
    $app->map(['GET'], '/contact/datatables', 'Admin:contactDatatables')
        ->setName('admin-contact-datatables');
    // Admin Lowongan
    $app->group('/job', function () use ($app) {
        // Main Blog Admin
        $app->get('', 'AdminJob:job')
        ->setName('admin-job');

        $app->get('/datatables', 'AdminJob:datatables')
        ->setName('admin-job-datatables');

        // Blog Post Actions
        $app->group('', function () use ($app) {
            // Unpublish Blog
            $app->post('/unpublish', 'AdminJob:jobUnpublish')
                ->setName('admin-job-unpublish');
            // Publish Blog
            $app->post('/publish', 'AdminJob:jobPublish')
                ->setName('admin-job-publish');
            // Edit Blog Post
            $app->map(['GET', 'POST'], '/edit[/{post_id}]', 'AdminJob:jobEdit')
                ->setName('admin-job-edit');
            // Add Blog Post
            $app->map(['GET', 'POST'], '/add', 'AdminJob:jobAdd')
                ->setName('admin-job-add');
            // Delete Blog Post
            $app->post('/delete', 'AdminJob:jobDelete')
                ->setName('admin-job-delete');
        });

                // Blog Comments
        $app->group('/pelamar', function () use ($app) {
            // View Comments
            $app->get('', 'AdminJobApply:apply')
                ->setName('admin-job-apply');
            $app->get('-{username}', 'AdminJobApply:CVJobseeker')
            ->setName('admin-job-apply-author');
            $app->get('/prosses', 'AdminJobApply:applyprosses')
                ->setName('admin-job-apply-prosses');
            // Datatables
            $app->get('/datatables', 'AdminJobApply:datatables')
                ->setName('admin-job-apply-datatables');
            $app->get('/listprosses', 'AdminJobApply:diprosses')
                ->setName('admin-job-apply-diprosses');
            $app->get('/listcandidate', 'AdminJobApply:listcandidate')
                ->setName('admin-job-apply-listcandidate');
            $app->get('/listreceived', 'AdminJobApply:listreceived')
                ->setName('admin-job-apply-listreceived');
            $app->get('/listout', 'AdminJobApply:listout')
                ->setName('admin-job-apply-listout');
            $app->get('/{comment_id}', 'AdminJobApply:applyDetails')
                ->setName('admin-job-apply-details');
            // Unpublish Comment
            $app->post('/unpublish', 'AdminJobApply:applyUnpublish')
                ->setName('admin-job-apply-unpublish');
            // Publish Comment
            $app->post('/publish', 'AdminJobApply:applyPublish')
                ->setName('admin-job-apply-publish');
            $app->post('/out', 'AdminJobApply:applyOut')
                ->setName('admin-job-apply-out');

            $app->post('/applycandidate', 'AdminJobApply:applyCandidate')
                ->setName('admin-job-apply-candidate');
            $app->post('/applyreceived', 'AdminJobApply:applyReceived')
                ->setName('admin-job-apply-received');

            // Delte Comment
            $app->post('/delete', 'AdminJobApply:applyDelete')
                ->setName('admin-job-apply-delete');
        });

        // Blog Replies
        $app->group('/replies', function () use ($app) {

            // Unpublish Comment
            $app->post('/publish', 'AdminBlogComments:replyUnpublish')
                ->setName('admin-job-reply-unpublish');
            // Publish Comment
            $app->post('/unpublish', 'AdminBlogComments:replyPublish')
                ->setName('admin-job-reply-publish');
            // Delte Comment
            $app->post('/delete', 'AdminBlogComments:replyDelete')
                ->setName('admin-job-reply-delete');
        });




        // Blog Categories Actions
        $app->group('/categories', function () use ($app) {
            // Delete Category
            $app->post('/delete', 'AdminJobCategories:categoriesDelete')
                ->setName('admin-job-categories-delete');
            // Edit Category
            $app->map(['GET', 'POST'], '/edit[/{category}]', 'AdminJobCategories:categoriesEdit')
                ->setName('admin-job-categories-edit');
            // Add Category
            $app->post('/add', 'AdminJobCategories:categoriesAdd')
                ->setName('admin-job-categories-add');
        });

        // Blog Tag Actions
        $app->group('/tags', function () use ($app) {
            // Delete Tag
            $app->post('/delete', 'AdminJobTags:tagsDelete')
                ->setName('admin-job-tags-delete');
            // Edit Tag
            $app->map(['GET', 'POST'], '/edit[/{tag_id}]', 'AdminJobTags:tagsEdit')
                ->setName('admin-job-tags-edit');
            // Delete Tag
            $app->post('/add', 'AdminBlogTags:tagsAdd')
                ->setName('admin-job-tags-add');
        });

        // Preview Blog Post
        $app->get('/preview[/{slug}]', 'AdminJob:jobPreview')
            ->setName('admin-job-preview');
    })
    ->add(new App\Middleware\BkolProfileCheck($container));
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));
