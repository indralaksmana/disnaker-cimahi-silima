<?php
// Route for Admin Perguruan Tinggi
$app->group('/dashboard/admin/bkol/perguruan-tinggi', function () use ($app, $container) {
    $app->get('', 'BkolPerguruanTinggi:index')
        ->setName('admin-bkol-perguruan-tinggi');
    
    // Delete
    $app->post('/delete', 'BkolPerguruanTinggi:delete')
        ->setName('admin-bkol-perguruan-tinggi-delete');
    
    // Edit
    $app->map(['GET', 'POST'], '/edit[/{id}]', 'BkolPerguruanTinggi:edit')
        ->setName('admin-bkol-perguruan-tinggi-edit');

    // Add
    $app->map(['GET', 'POST'], '/add', 'BkolPerguruanTinggi:add')
        ->setName('admin-bkol-perguruan-tinggi-add');

    $app->post('/aktifkan', 'BkolPerguruanTinggi:aktifkan')
    ->setName('admin-bkol-perguruan-tinggi-aktifkan');
    $app->post('/nonaktifkan', 'BkolPerguruanTinggi:nonaktifkan')
    ->setName('admin-bkol-perguruan-tinggi-nonaktifkan');
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));

// Route for Admin LPK
$app->group('/dashboard/admin/bkol/lpk', function () use ($app, $container) {
    $app->get('', 'BkolLpk:index')
        ->setName('admin-bkol-lpk');
    
    // Delete
    $app->post('/delete', 'BkolLpk:delete')
        ->setName('admin-bkol-lpk-delete');
    
    // Edit
    $app->map(['GET', 'POST'], '/edit[/{id}]', 'BkolLpk:edit')
        ->setName('admin-bkol-lpk-edit');

    // Add
    $app->map(['GET', 'POST'], '/add', 'BkolLpk:add')
        ->setName('admin-bkol-lpk-add');

    $app->post('/aktifkan', 'BkolLpk:aktifkan')
    ->setName('admin-bkol-lpk-aktifkan');
    $app->post('/nonaktifkan', 'BkolLpk:nonaktifkan')
    ->setName('admin-bkol-lpk-nonaktifkan');
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));

// Route for Admin BKK
$app->group('/dashboard/admin/bkol/bkk', function () use ($app, $container) {
    $app->get('', 'BkolBkk:index')
        ->setName('admin-bkol-bkk');
    
    // Delete
    $app->post('/delete', 'BkolBkk:delete')
        ->setName('admin-bkol-bkk-delete');
    
    // Edit
    $app->map(['GET', 'POST'], '/edit[/{id}]', 'BkolBkk:edit')
        ->setName('admin-bkol-bkk-edit');

    // Add
    $app->map(['GET', 'POST'], '/add', 'BkolBkk:add')
        ->setName('admin-bkol-bkk-add');

    $app->post('/aktifkan', 'BkolBkk:aktifkan')
    ->setName('admin-bkol-bkk-aktifkan');
    $app->post('/nonaktifkan', 'BkolBkk:nonaktifkan')
    ->setName('admin-bkol-bkk-nonaktifkan');
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));

// Route for Admin Pemerintah
$app->group('/dashboard/admin/bkol/pemerintah', function () use ($app, $container) {
    $app->get('', 'BkolPemerintah:index')
        ->setName('admin-bkol-pemerintah');
    
    // Delete
    $app->post('/delete', 'BkolPemerintah:delete')
        ->setName('admin-bkol-pemerintah-delete');
    
    // Edit
    $app->map(['GET', 'POST'], '/edit[/{id}]', 'BkolPemerintah:edit')
        ->setName('admin-bkol-pemerintah-edit');

    // Add
    $app->map(['GET', 'POST'], '/add', 'BkolPemerintah:add')
        ->setName('admin-bkol-pemerintah-add');

    $app->post('/aktifkan', 'BkolPemerintah:aktifkan')
    ->setName('admin-bkol-pemerintah-aktifkan');
    $app->post('/nonaktifkan', 'BkolPemerintah:nonaktifkan')
    ->setName('admin-bkol-pemerintah-nonaktifkan');
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));
