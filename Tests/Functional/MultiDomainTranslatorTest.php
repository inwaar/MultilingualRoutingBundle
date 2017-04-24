<?php

namespace MultilingualRoutingBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\TranslatorInterface;

class MultiDomainTranslatorTest extends KernelTestCase
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var RequestContext */
    private $requestContext;

    protected function setUp()
    {
        require_once __DIR__ . '/AppKernel.php';

        $kernel = new AppKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->translator = $container->get('translator');
        $this->requestContext = $container->get('router.request_context');
    }

    public function testTranslating()
    {
        $this->assertEquals('en default', $this->translator->trans('hello'));
        $this->assertEquals('ua default', $this->translator->trans('hello', [], null, 'ua'));

        $this->requestContext->setHost('example.com');
        $this->translator->setLocale('en');

        $this->assertEquals('en', $this->translator->trans('hello'));
        $this->assertEquals('ua', $this->translator->trans('hello', [], null, 'ua'));

        $this->assertEquals('en', $this->translator->trans('hello'));

        $this->translator->setLocale('ua');
        $this->assertEquals('ua', $this->translator->trans('hello'));
    }

    public function testFallingBack()
    {
        $this->requestContext->setHost('example.com');
        $this->translator->setLocale('en');
        $this->assertEquals('en default', $this->translator->trans('bye'));
        $this->assertEquals('ua default', $this->translator->trans('bye', [], null, 'ua'));
    }

    public function testNoTranslations()
    {
        $this->requestContext->setHost('example.com');
        $this->translator->setLocale('en');

        $this->assertEquals('no', $this->translator->trans('no'));
        $this->assertEquals('no', $this->translator->trans('no', [], null, 'ua'));
    }
}
