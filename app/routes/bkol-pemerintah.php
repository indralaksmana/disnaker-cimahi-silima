<?php

$app->group('/pemerintah', function () use ($app, $container) {
  $this->get('', 'MinisitePemerintah:index')
  ->setName('minisite-pemerintah');
  $this->map(['GET'], '/{slug}/', 'MinisitePemerintah:detail')
  ->setName('minisite-pemerintah-detail');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
