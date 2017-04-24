<?php

namespace MultilingualRoutingBundle\Translation;

use MultilingualRoutingBundle\Routing\Mapper\HostLocaleMapper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MultiDomainTranslator implements TranslatorInterface, TranslatorBagInterface
{
    /** @var TranslatorInterface|TranslatorBagInterface */
    private $translator;

    /** @var string */
    private $tldDomain;

    /** @var RequestContext */
    private $requestContext;

    /** @var HostLocaleMapper */
    private $mapper;

    /**
     * @param TranslatorBagInterface|TranslatorInterface $translator
     * @param RequestContext $requestContext
     * @param HostLocaleMapper $mapper
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestContext $requestContext,
        HostLocaleMapper $mapper
    ) {
        $this->translator = $translator;
        $this->requestContext = $requestContext;
        $this->mapper = $mapper;
    }

    /**
     * @inheritdoc
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $tldDomain = $this->getTranslationDomain($domain);

        if ($this->translator->getCatalogue($locale)->defines((string)$id, $tldDomain)) {
            return $this->translator->trans($id, $parameters, $tldDomain, $locale);
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @inheritdoc
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $tldDomain = $this->getTranslationDomain($domain);

        if ($this->translator->getCatalogue($locale)->defines((string)$id, $tldDomain)) {
            return $this->translator->transChoice($id, $number, $parameters, $tldDomain, $locale);
        }

        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * @inheritdoc
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
        $this->mapper->setRequestContext($this->requestContext);
        $this->tldDomain = $this->mapper->getBaseHost();
    }

    /**
     * Replace or prepent tld domain to the initial translation domain
     *
     * @param string|null $domain given initial translation domain
     * @return string converted translation domain ready to be passed to the default translator
     */
    protected function getTranslationDomain($domain): string
    {
        $parts = [];
        if ($this->tldDomain) {
            $parts[] = $this->tldDomain;
        }

        if ($domain && $domain !== 'messages') {
            $parts[] = $domain;
        }

        return implode('_', $parts);
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
        $this->translator->getLocale();
    }

    /**
     * @inheritdoc
     */
    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }
}
