<?php

namespace MultilingualRoutingBundle\Routing\Mapper;

use Symfony\Component\Routing\RequestContext;

class PostfixMapper implements HostLocaleMapper
{
    const PATTERN = '/(.*)\.(.+)$/';
    const HOST_MATCH_INDEX = 1;
    const LOCALE_MATCH_INDEX = 2;

    /** @var RequestContext */
    private $requestContext;
    /** @var string */
    private $defaultLocale;
    /** @var string[] */
    private $map;
    /** @var string[] */
    private $flippedMap;

    /**
     * @param string $defaultLocale
     * @param string[] $map
     */
    public function __construct(string $defaultLocale, $map = [])
    {
        $this->defaultLocale = $defaultLocale;
        $this->map = $map;
        $this->flippedMap = array_flip($this->map);
    }

    /**
     * @inheritdoc
     */
    public function setRequestContext(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHost(string $locale): string
    {
        return sprintf('%s.%s', $this->getBaseHost(), $this->map[$locale] ?? $locale);
    }

    /**
     * @return string
     */
    public function getBaseHost(): string
    {
        $contextHost = $this->requestContext->getHost();

        foreach ($this->map as $tld) {
            $tldPosition = strlen($contextHost) - strlen($tld);
            if (strrpos($contextHost, $tld) === $tldPosition) {
                return substr($contextHost, 0, $tldPosition - 1);
            }
        }

        if (preg_match(self::PATTERN, $contextHost, $matches)) {
            return $matches[self::HOST_MATCH_INDEX];
        }

        return $contextHost;
    }

    /**
     * @inheritdoc
     */
    public function getLocale(string $host): string
    {
        foreach ($this->flippedMap as $tld => $locale) {
            if (strlen($host) - strlen($tld) === strrpos($host, $tld)) {
                return $locale;
            }
        }

        if (preg_match(self::PATTERN, $host, $matches)) {
            $locale = $matches[self::LOCALE_MATCH_INDEX];
        } else {
            $locale = $this->defaultLocale;
        }

        return $locale;
    }
}
