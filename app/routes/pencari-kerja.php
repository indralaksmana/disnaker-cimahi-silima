<?php
// PErusahaan
$app->group('/u', function () use ($app) {
  $this->map(['GET', 'POST'], '[/]', 'MinisitePencariKerja:main')->setName('list-pencarikerja');
  $this->map(['GET', 'POST'], '/list', 'MinisitePencariKerja:list')->setName('list-pencarikerja');
  $this->map(['GET', 'POST'], '/follow/{id}', 'MinisitePencariKerja:Follow')
  ->setName('pencarikerja-follow');
  $this->map(['GET', 'POST'], '/{username}', 'MinisitePencariKerja:Detail')
  ->setName('pencarikerja-detail');


  $this->get('/kodejurusan/{kodependidikan}', 'MinisitePencariKerja:KodeJurusan')
  ->setName('kodejurusan');
  $this->get('//kodependidikan', 'MinisitePencariKerja:KodePendidikan')
  ->setName('kodependidikan');
})
->add($container->get('csrf'))
->add(new App\Middleware\Maintenance($container))
->add(new App\Middleware\PageConfig($container))
->add(new App\Middleware\Seo($container));
