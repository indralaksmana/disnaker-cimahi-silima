<?php

namespace App\Library;

use Psr\Container\ContainerInterface;

class Library
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}
