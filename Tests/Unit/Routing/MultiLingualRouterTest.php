<?php

namespace MultilingualRoutingBundle\Tests\Unit\Routing;

use MultilingualRoutingBundle\Routing\Mapper\HostLocaleMapper;
use MultilingualRoutingBundle\Routing\MultiLingualRouter;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

class MultiLingualRouterTest extends TestCase
{
    /** @var RouteCollection|PHPUnit_Framework_MockObject_MockObject */
    protected $routeCollection;

    /** @var Router|PHPUnit_Framework_MockObject_MockObject */
    protected $defaultRouter;

    /** @var HostLocaleMapper|PHPUnit_Framework_MockObject_MockObject */
    protected $mapper;

    /** @var RequestContext|PHPUnit_Framework_MockObject_MockObject */
    protected $requestContext;

    /** @var MultiLingualRouter|PHPUnit_Framework_MockObject_MockObject */
    protected $router;

    protected function setUp()
    {
        $this->requestContext = $this->createMock(RequestContext::class);

        $this->routeCollection = $this->createMock(RouteCollection::class);

        $this->defaultRouter = $this->createMock(Router::class);
        $this->defaultRouter->expects($this->any())
            ->method('getContext')
            ->willReturn($this->requestContext);

        $this->defaultRouter->expects($this->any())
            ->method('getRouteCollection')
            ->willReturn($this->routeCollection);

        $this->mapper = $this->createMock(HostLocaleMapper::class);

        $this->router = new MultiLingualRouter($this->defaultRouter, 'en', $this->mapper);
        $this->router->setContext($this->requestContext);
    }

    public function localeVariantsProvider()
    {
        return [
            ['/{_locale}', 'en', 'en', 'en'],
            ['/{_locale}', 'en', 'nl', 'en'],
            ['/{_locale}', 'nl', 'nl', 'en'],
            ['/page', 'en', 'en', 'en'],
            ['/page', 'en', 'nl', 'en'],
            ['/page', 'nl', 'en', 'en'],
            ['/page', 'nl', 'nl', 'nl'],
        ];
    }

    /**
     * @param $route - route
     * @param $contextLocale - context locale
     * @param $urlLocale - locale for a given URL to generate
     * @param $mapperLocale - expected locale to be used for querying a mapper
     *
     * @dataProvider localeVariantsProvider
     */
    public function testShouldUseDefaultLocaleWhenInQueryOrParameters($route, $contextLocale, $urlLocale, $mapperLocale)
    {
        $this->routeCollection->expects($this->any())
            ->method('get')
            ->willReturn(new Route($route));

        $this->requestContext->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');

        $this->requestContext->expects($this->any())
            ->method('getParameter')
            ->with('_locale')
            ->willReturn($contextLocale);

        $this->mapper->expects($this->once())
            ->method('getHost')
            ->with($mapperLocale);

        $this->router->generate('home', ['_locale' => $urlLocale]);
    }

    public function testShouldPreserveRequestContextHost()
    {
        $this->routeCollection->expects($this->once())
            ->method('get')
            ->willReturn(new Route('/'));

        $this->requestContext->expects($this->once())
            ->method('getHost')
            ->willReturn('original.com');

        $this->mapper->expects($this->once())
            ->method('getHost')
            ->willReturn('new.com');

        $this->requestContext->expects($this->exactly(2))
            ->method('setHost')
            ->withConsecutive(
                [$this->equalTo('new.com')],
                [$this->equalTo('original.com')]
            );

        $this->router->generate('home');
    }

    public function testShouldUseMapperToDetectLocaleFromHostnameWhenNoLocaleInRequestQuery()
    {
        $this->defaultRouter->expects($this->exactly(2))
            ->method('matchRequest')
            ->willReturnOnConsecutiveCalls([], ['_locale' => 'en']);

        $this->mapper->expects($this->once())
            ->method('getLocale');

        $this->router->matchRequest(new Request());
        $this->router->matchRequest(new Request());
    }

    public function testShouldDecorateDefaultRouter()
    {
        $this->defaultRouter->expects($this->once())
            ->method('match');
        $this->defaultRouter->expects($this->once())
            ->method('getContext');
        $this->defaultRouter->expects($this->once())
            ->method('setContext');

        $this->router->match('/');
        $this->router->getContext();
        $this->router->setContext($this->requestContext);
    }

    public function testThrowExceptionIfRouteNotFound()
    {
        $this->routeCollection->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->expectException(RouteNotFoundException::class);

        $this->router->generate('home');
    }
}
