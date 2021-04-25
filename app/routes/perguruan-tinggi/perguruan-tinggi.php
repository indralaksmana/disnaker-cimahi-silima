<?php
// Front BKK
// Front BKK
$app->group('/dikti', function () use ($app, $container) {
  $this->map(['GET'], '', 'MinisitePerguruanTinggi:index')
  ->setName('minisite-pt');
  $this->map(['GET'], '/{slug}/', 'MinisitePerguruanTinggi:Detail')
  ->setName('minisite-pt-detail');
  $this->get('/{slug}/agenda/', 'MinisitePerguruanTinggi:agenda')
  ->setName('minisite-pt-agenda');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));

// BKK DASHBOARD
include('perguruan-tinggi-dashboard.php');