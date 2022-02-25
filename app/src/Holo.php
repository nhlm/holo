<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */
namespace Holo;

use League\Container\Container as DepdenencyContainer;

class Holo {

    public function __construct(DependencyContainer $container = null)
    {
        $this->container = $container ?? new DependencyContainer();
        $this->container->addServiceProvider(new HoloServiceProvider());
    }

}
