<?php
// Front BKK
$app->group('/lpk', function () use ($app, $container) {
  $this->map(['GET'], '', 'MinisiteLpk:index')
  ->setName('minisite-lpk');
  $this->get('/{slug}/', 'MinisiteLpk:detail')
  ->setName('minisite-lpk-detail');
  $this->get('/{slug}/agenda/', 'MinisiteLpk:agenda')
  ->setName('minisite-lpk-agenda');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));

// BKK DASHBOARD
include('lpk-dashboard.php');