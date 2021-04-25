<?php
// Requires Authentication
$app->group('', function () use ($app) {
    // User Profile
    $app->group('/profile', function () use ($app) {
        //Profile
        $this->map(['GET', 'POST'], '', 'Profile:profile')
            ->setName('profile');
        // Check Password
        $this->map(['POST'], '/password-check', 'Profile:checkPassword')
            ->setName('password-check');
        // Change Password
        $this->map(['POST'], '/change-password', 'Profile:changePassword')
            ->setName('change-password');
         $app->group('/2fa', function () use ($app) {
            $this->post('[/{validate}]', 'Profile:twoFactor')
                ->setName('2fa');
         });
    });
})
->add($container->get('csrf'))
->add(new App\Middleware\Auth($container))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
// ->add(new App\Middleware\ProfileCheck($container));

// Incomplete Profile Page
$app->map(['GET','POST'], '/profile/incomplete', 'Profile:profileIncomplete')
        ->setName('profile-incomplete')
        ->add($container->get('csrf'))
        ->add(new App\Middleware\Auth($container))
        ->add(new App\Middleware\Maintenance($container))
        ->add(new App\Middleware\PageConfig($container))
        ->add(new App\Middleware\Seo($container));
