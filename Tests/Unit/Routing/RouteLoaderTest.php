<?php

namespace MultilingualRoutingBundle\Tests\Unit\Routing;

use MultilingualRoutingBundle\Routing\RouteLoader;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoaderTest extends TestCase
{
    /** @var RouteLoader|PHPUnit_Framework_MockObject_MockObject */
    private $routeLoader;

    /** @var LoaderInterface|PHPUnit_Framework_MockObject_MockObject */
    private $loader;

    /** @var Route|PHPUnit_Framework_MockObject_MockObject */
    private $route;

    /** @var RouteCollection */
    private $routeCollection;

    protected function setUp()
    {
        $this->routeCollection = new RouteCollection();

        $this->loader = $this->createMock(LoaderInterface::class);
        $this->loader->expects($this->any())
            ->method('load')
            ->willReturn($this->routeCollection);

        $this->routeLoader = $this->createPartialMock(RouteLoader::class, ['resolve']);
        $this->routeLoader->expects($this->any())
            ->method('resolve')
            ->willReturn($this->loader);

        $this->route = $this->createMock(Route::class);
        $this->routeCollection->add('default', $this->route);
        $this->route->expects($this->any())
            ->method('getPath')
            ->willReturn('/');
    }

    public function testLoad()
    {
        $this->route->expects($this->once())
            ->method('setPath');
        $this->route->expects($this->once())
            ->method('setDefault');
        $this->route->expects($this->once())
            ->method('setRequirement');

        $this->routeLoader->load('.');
    }

    public function testSupport()
    {
        $this->assertTrue($this->routeLoader->supports('.', 'localized'));
    }
}
