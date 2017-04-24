<?php

namespace MultilingualRoutingBundle\Routing;

use MultilingualRoutingBundle\Routing\Mapper\HostLocaleMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class MultiLingualRouter implements RouterInterface, RequestMatcherInterface
{
    /** @var RouterInterface|RequestMatcherInterface */
    private $defaultRouter;
    /** @var HostLocaleMapper */
    private $mapper;
    /** @var string */
    private $defaultLocale;

    /**
     * @param RouterInterface $defaultRouter
     * @param HostLocaleMapper $mapper
     * @param string $defaultLocale
     */
    public function __construct(RouterInterface $defaultRouter, string $defaultLocale, HostLocaleMapper $mapper)
    {
        $this->defaultRouter = $defaultRouter;
        $this->defaultLocale = $defaultLocale;
        $this->mapper = $mapper;
    }

    /**
     * @inheritdoc
     */
    public function match($pathinfo)
    {
        $this->defaultRouter->match($pathinfo);
    }

    /**
     * @inheritdoc
     */
    public function setContext(RequestContext $context)
    {
        $this->defaultRouter->setContext($context);
    }

    /**
     * @inheritdoc
     */
    public function getContext()
    {
        return $this->defaultRouter->getContext();
    }

    /**
     * @inheritdoc
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $context = $this->defaultRouter->getContext();
        $this->mapper->setRequestContext($context);

        if ($this->parametersIncludeDefaultLocale($parameters) || $this->routePathIncludesLocale($name)) {
            $host = $this->mapper->getHost($this->defaultLocale);
        } else {
            $host = $this->mapper->getHost($context->getParameter('_locale') ?? $this->defaultLocale);
        }

        return $this->stackContextHost($context, $host, function () use ($name, $parameters) {
            return $this->defaultRouter->generate($name, $parameters, self::ABSOLUTE_URL);
        });
    }

    /**
     * @inheritdoc
     */
    public function getRouteCollection()
    {
        return $this->defaultRouter->getRouteCollection();
    }

    /**
     * @inheritdoc
     */
    public function matchRequest(Request $request)
    {
        $parameters = $this->defaultRouter->matchRequest($request);
        $parameters['_locale'] = $parameters['_locale'] ?? $this->mapper->getLocale($request->getHost());
        return $parameters;
    }

    /**
     * @param string $name
     * @return bool
     *
     * @throws RouteNotFoundException
     */
    private function routePathIncludesLocale(string $name): bool
    {
        $route = $this->getRouteCollection()->get($name);
        if (!$route) {
            throw new RouteNotFoundException();
        }
        return $route && strpos($route->getPath(), '{_locale}') !== false;
    }

    /**
     * @param string[] $parameters
     * @return bool
     */
    private function parametersIncludeDefaultLocale(array $parameters): bool
    {
        $urlLocale = $parameters['_locale'] ?? null;
        return $urlLocale && $urlLocale === $this->defaultLocale;
    }

    /**
     * @param RequestContext $context
     * @param string $host
     * @param callable $func
     * @return mixed
     */
    private function stackContextHost(RequestContext $context, string $host, callable $func)
    {
        $preserveHost = $context->getHost();

        $context->setHost($host);
        $result = $func();
        $context->setHost($preserveHost);

        return $result;
    }
}
