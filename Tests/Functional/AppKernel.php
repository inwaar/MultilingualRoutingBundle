<?php

namespace MultilingualRoutingBundle\Tests\Functional;

use MultilingualRoutingBundle\MultilingualRoutingBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [];
        $bundles[] = new FrameworkBundle();
        $bundles[] = new MultilingualRoutingBundle();
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/MultilingualRoutingBundle/cache';
    }
    public function getLogDir()
    {
        return sys_get_temp_dir().'/MultilingualRoutingBundle/logs';
    }
}
