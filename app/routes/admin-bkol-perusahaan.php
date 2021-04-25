<?php

$app->group('/dashboard/admin/bkol/perusahaan', function () use ($app, $container) {
    $app->get('', 'BkolPerusahaan:index')
        ->setName('bkol-perusahaan');
    // Delete
    $app->post('/delete', 'BkolPerusahaan:Delete')
        ->setName('bkol-perusahaan-delete');
    $app->post('/aktifkan', 'BkolPerusahaan:Aktifkan')
    ->setName('bkol-perusahaan-aktifkan');
    $app->post('/nonaktifkan', 'BkolPerusahaan:NonAktifkan')
    ->setName('bkol-perusahaan-nonaktifkan');
    // Edit
    $app->map(['GET', 'POST'], '/edit[/{id}]', 'BkolPerusahaan:edit')
        ->setName('bkol-perusahaan-edit');
    // Detail
    $app->get('/detail[/{bkol-perusahaan}]', 'BkolPerusahaan:DetailBkolPerusahaan')
    ->setName('detail-bkol-perusahaan');
    // Add
    $app->map(['GET', 'POST'], '/add', 'BkolPerusahaan:add')
        ->setName('bkol-perusahaan-add');
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));
