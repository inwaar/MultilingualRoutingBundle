<?php

namespace MultilingualRoutingBundle\Tests\Functional;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class MultiLingualRouterTest extends KernelTestCase
{
    /** @var RouterInterface|RequestMatcherInterface */
    private $router;
    /** @var RequestContext */
    private $requestContext;

    protected function setUp()
    {
        require_once __DIR__ . '/AppKernel.php';

        $kernel = new AppKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->router = $container->get('router');
        $this->requestContext = $container->get('router.request_context');
    }

    public function correctUrlsDataProvider()
    {
        return [
            ['example.com', 'nl', 'internal', 'http://example.nl/internal'],
            ['example.com', 'en', 'internal', 'http://example.com/internal'],
            ['example.nl', 'en', 'internal', 'http://example.com/internal'],
            ['example.nl', 'nl', 'internal', 'http://example.nl/internal'],
            ['more.example.com', 'en', 'home', 'http://more.example.com/'],
            ['more.example.com', 'en', 'internal', 'http://more.example.com/internal'],
            ['more.example.fr', 'nl', 'internal', 'http://more.example.nl/internal'],
            ['example.com', 'en_GB', 'internal', 'http://example.co.uk/internal'],
            ['more.example.com', 'en_GB', 'internal', 'http://more.example.co.uk/internal'],
        ];
    }

    /**
     * @param $host - the current host name
     * @param $locale - the current locale
     * @param $route - given route name
     * @param $url - expected URL
     *
     * @dataProvider correctUrlsDataProvider
     */
    public function testShouldGenerateCorrectUrls($host, $locale, $route, $url)
    {
        $this->requestContext->setHost($host);
        $this->requestContext->setParameter('_locale', $locale);

        $this->assertEquals($url, $this->router->generate($route));
    }

    public function localeChangeUrlsDataProvider()
    {
        return [
            ['example.com', 'en', 'en', 'http://example.com/'],
            ['example.com', 'en', 'nl', 'http://example.com/nl'],
            ['example.com', 'nl', 'nl', 'http://example.com/nl'],
            ['example.fr', 'nl', 'nl', 'http://example.com/nl'],
            ['example.fr', 'nl', 'nl', 'http://example.com/nl'],
            ['more.example.fr', 'nl', 'en', 'http://more.example.com/'],
        ];
    }

    /**
     * @param $host - current host name
     * @param $locale - current locale
     * @param $linkLocale - required locale to be used for generating the URL
     * @param $url - expected URL
     *
     * @dataProvider localeChangeUrlsDataProvider
     */
    public function testShouldGenerateRootLocaleUrlUsingDefaultLocale($host, $locale, $linkLocale, $url)
    {
        $this->requestContext->setHost($host);
        $this->requestContext->setParameter('_locale', $locale);

        $this->assertEquals($url, $this->router->generate('home', ['_locale' => $linkLocale]));
    }

    public function matchLocaleFromRequestQueryDataProvider()
    {
        return [
            ['http://example.com/nl', 'home', 'nl'],
            ['http://example.com', 'home', 'en'],
            ['http://example.nl/internal', 'internal', 'nl'],
            ['http://example.com/internal', 'internal', 'en'],
            ['http://example.co.uk/internal', 'internal', 'en_GB'],
        ];
    }

    /**
     * @param $uri - current URI
     * @param $route - expected route
     * @param $locale - expected locale
     *
     * @dataProvider matchLocaleFromRequestQueryDataProvider
     */
    public function testShouldMatchLocaleFromDomainAndQuery($uri, $route, $locale)
    {
        $return = $this->router->matchRequest(Request::create($uri));

        $this->assertEquals($route, $return['_route']);
        $this->assertEquals($locale, $return['_locale']);
    }
}
