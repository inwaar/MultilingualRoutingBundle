<?php

namespace MultilingualRoutingBundle\Tests\Unit\Routing\Mapper;

use MultilingualRoutingBundle\Routing\Mapper\HostLocaleMapper;
use MultilingualRoutingBundle\Routing\Mapper\PostfixMapper;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\RequestContext;

class PostfixMapperTest extends TestCase
{
    /** @var string */
    protected $defaultLocale = 'en';

    /** @var RequestContext|PHPUnit_Framework_MockObject_MockObject */
    protected $requestContext;

    /** @var HostLocaleMapper */
    private $mapper;

    protected function setUp()
    {
        $this->requestContext = $this->createMock(RequestContext::class);

        $this->mapper = new PostfixMapper($this->defaultLocale, [
            'en' => 'com',
            'en_GB' => 'co.uk'
        ]);

        $this->mapper->setRequestContext($this->requestContext);
    }

    public function hostToLocaleProvider()
    {
        return [
            ['example.com', 'en'],
            ['example.nl', 'nl'],
            ['example.co.uk', 'en_GB'],
        ];
    }

    /**
     * @param $host
     * @param $locale
     *
     * @dataProvider hostToLocaleProvider
     */
    public function testShouldMapHostToLocale($host, $locale)
    {
        $this->assertEquals($locale, $this->mapper->getLocale($host));
    }

    public function testShouldMapToDefaultLocale()
    {
        $this->assertEquals($this->defaultLocale, $this->mapper->getLocale('localhost'));
    }

    /**
     * @param $host
     * @param $locale
     *
     * @dataProvider hostToLocaleProvider
     */
    public function testShouldLocaleToHost($host, $locale)
    {
        $this->requestContext->expects($this->any())
            ->method('getHost')
            ->willReturn('example.co.uk');

        $this->assertEquals($host, $this->mapper->getHost($locale));
    }

    public function testShouldMapToContextHost()
    {
        $this->requestContext->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost');

        $this->assertEquals('localhost.com', $this->mapper->getHost('en'));
    }
}
