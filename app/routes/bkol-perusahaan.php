<?php



$app->group('/p', function () use ($app, $container) {
  $this->get('', 'MinisitePerusahaan:index')
  ->setName('minisite-perusahaan');
  $this->map(['GET'], '/{slug}/', 'MinisitePerusahaan:Detail')
  ->setName('minisite-perusahaan-detail');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
