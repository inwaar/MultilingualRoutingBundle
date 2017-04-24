<?php

namespace MultilingualRoutingBundle\Routing\Mapper;

use Symfony\Component\Routing\RequestContext;

interface HostLocaleMapper
{
    /**
     * Set a request context which can be used by the mapper to determine a host or a locale
     *
     * @param RequestContext $requestContext
     * @return HostLocaleMapper
     */
    public function setRequestContext(RequestContext $requestContext);

    /**
     * Determines a host from a given locale, can use the request context
     *
     * @param string $locale
     * @return string
     */
    public function getHost(string $locale): string;

    /**
     * The same as `getHost` but without locale postfix
     *
     * @return string
     */
    public function getBaseHost(): string;

    /**
     * Determines a locale from the given host name
     *
     * @param string $host
     * @return string
     */
    public function getLocale(string $host): string;
}
