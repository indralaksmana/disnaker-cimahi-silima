<?php
// Blog Front End
$app->group('/bkk-dan-lpk', function () use ($app, $container) {
  $this->map(['GET'], '', 'BkkLpk:bkk')
  ->setName('bkol-bkk');
  $this->map(['GET', 'POST'], '/detail-bkk/{id}', 'BkkLpk:BkkDetail')
  ->setName('bkk-detail');
  $this->map(['GET', 'POST'], '/detail-lpk/{id}', 'BkkLpk:LpkDetail')
  ->setName('lpk-detail');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
