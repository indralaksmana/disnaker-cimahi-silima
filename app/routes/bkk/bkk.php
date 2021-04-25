<?php
// Front BKK
$app->group('/bkk', function () use ($app, $container) {
  $this->map(['GET'], '', 'MinisiteBkk:index')
  ->setName('minisite-bkk');
  $this->get('/{slug}/', 'MinisiteBkk:detail')
  ->setName('minisite-bkk-detail');
  $this->get('/{slug}/agenda/', 'MinisiteBkk:agenda')
  ->setName('minisite-bkk-agenda');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));

// BKK DASHBOARD
include('bkk-dashboard.php');