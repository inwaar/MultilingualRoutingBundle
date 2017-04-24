<?php

namespace MultilingualRoutingBundle\Tests\Unit\Translation;

use MultilingualRoutingBundle\Routing\Mapper\HostLocaleMapper;
use MultilingualRoutingBundle\Translation\MultiDomainTranslator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Translator;

class MultiDomainTranslatorTest extends TestCase
{
    /** @var MultiDomainTranslator */
    private $translator;

    /** @var Translator|PHPUnit_Framework_MockObject_MockObject */
    private $defaultTranslator;

    /** @var MessageCatalogue|PHPUnit_Framework_MockObject_MockObject */
    private $messageCatalogue;

    /** @var HostLocaleMapper|PHPUnit_Framework_MockObject_MockObject */
    private $mapper;

    /** @var RequestContext|PHPUnit_Framework_MockObject_MockObject */
    private $requestContext;

    protected function setUp()
    {
        $this->messageCatalogue = $this->createMock(MessageCatalogue::class);

        $this->defaultTranslator = $this->createMock(Translator::class);
        $this->defaultTranslator->expects($this->any())
            ->method('getCatalogue')
            ->willReturn($this->messageCatalogue);

        $this->requestContext = $this->createMock(RequestContext::class);
        $this->mapper = $this->createMock(HostLocaleMapper::class);
        $this->mapper->expects($this->any())
            ->method('getBaseHost')
            ->willReturn('example');

        $this->translator = new MultiDomainTranslator($this->defaultTranslator, $this->requestContext, $this->mapper);
    }

    public function useTldAsTranslationDomainDataProvider()
    {
        return [
            [null, 'example'],
            ['messages', 'example'],
            ['backoffice', 'example_backoffice'],
        ];
    }

    /**
     * @param $domain - given translation domain
     * @param $defaultTranslatorDomain - translation domain passed to default translator
     *
     * @dataProvider useTldAsTranslationDomainDataProvider
     */
    public function testShouldUseTldAsTranslationDomain($domain, $defaultTranslatorDomain)
    {
        $this->messageCatalogue->expects($this->any())
            ->method('defines')
            ->willReturn(true);

        $this->defaultTranslator->expects($this->any())
            ->method('trans')
            ->with('key', [], $defaultTranslatorDomain);

        $this->translator->setLocale('en');
        $this->translator->trans('key', [], $domain);
        $this->translator->transChoice('key', 1, [], $domain);
    }

    public function testShouldFallbackToDefaultTranslationDomain()
    {
        $this->messageCatalogue->expects($this->any())
            ->method('defines')
            ->willReturn(false);

        $this->defaultTranslator->expects($this->once())
            ->method('trans')
            ->with('key', [], null, null);

        $this->translator->setLocale('en');
        $this->translator->trans('key', []);
        $this->translator->transChoice('key', 1, []);
    }

    public function testShouldDecorateDefaultTranslator()
    {
        $this->defaultTranslator->expects($this->once())
            ->method('getLocale');
        $this->defaultTranslator->expects($this->once())
            ->method('getCatalogue');

        $this->translator->getLocale();
        $this->translator->getCatalogue();
    }
}
