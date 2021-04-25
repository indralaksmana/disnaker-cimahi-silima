<?php

$app->group('/dashboard', function () use ($app, $container) {
    // Users Routes
    $app->group('/users', function () use ($app) {
        // User List
        $app->get('', 'AdminUsers:users')
            ->setName('admin-users');

        // Add New User
        $app->map(['GET', 'POST'], '/add', 'AdminUsers:usersAdd')
            ->setName('admin-users-add');

        // Add New User
        $app->map(['GET', 'POST'], '/add-lpk', 'AdminUsersLPK:usersAdd')
            ->setName('admin-users-lpk-add');

        $app->map(['GET', 'POST'], '/index-lpk', 'AdminUsersLPK:usersIndex')
            ->setName('admin-users-lpk-index');

        $app->map(['GET', 'POST'], '/add-bkk', 'AdminUsersBKK:usersAdd')
            ->setName('admin-users-bkk-add');

        $app->map(['GET', 'POST'], '/index-bkk', 'AdminUsersBKK:usersIndex')
            ->setName('admin-users-bkk-index');

        $app->map(['GET', 'POST'], '/index-pt', 'AdminUsers:usersIndexPT')
            ->setName('admin-users-pt-index');

         $app->map(['GET', 'POST'], '/add-pt', 'AdminUsers:usersAddPT')
            ->setName('admin-users-pt-add');

        // Edit User
        $app->map(['GET', 'POST'], '/edit/{user_id}', 'AdminUsers:usersEdit')
            ->setName('admin-users-edit');

        $app->map(['GET', 'POST'], '/edit-bkk/{user_id}', 'AdminUsersBKK:usersEdit')
            ->setName('admin-users-bkk-edit');

        $app->map(['GET', 'POST'], '/edit-lpk/{user_id}', 'AdminUsersLPK:usersEdit')
            ->setName('admin-users-lpk-edit');
        // Delete User
        $app->post('/delete', 'AdminUsers:usersDelete')
            ->setName('admin-users-delete');
        // User Ajax
        $app->get('/datatables', 'AdminUsers:dataTables')
            ->setName('admin-users-datatables');
        $app->get('/pencarikerja', 'AdminUsers:dataTablesPencariKerja')
            ->setName('admin-users-pencarikerja');
        $app->get('/perusahaan', 'AdminUsers:dataTablesPerusahaan')
            ->setName('admin-users-perusahaan');
        $app->get('/bkk', 'AdminUsers:dataTablesBKK')
            ->setName('admin-users-bkk');
        $app->get('/lpk', 'AdminUsers:dataTablesLPK')
            ->setName('admin-users-lpk');
        $app->get('/pt', 'AdminUsers:dataTablesPerguruanTinggi')
            ->setName('admin-users-pt');

        //User Roles
        $app->group('/roles', function () use ($app) {
          $app->get('', 'AdminRoles:roles')
              ->setName('admin-roles');
            $app->post('/delete', 'AdminRoles:rolesDelete')
                ->setName('admin-roles-delete');
            $app->map(['GET', 'POST'], '/edit/{role}', 'AdminRoles:rolesEdit')
                ->setName('admin-roles-edit');
            $app->post('/add', 'AdminRoles:rolesAdd')
                ->setName('admin-roles-add');
        });
    });
})
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Admin($container))
->add($container->get('csrf'));