<?php

namespace MultilingualRoutingBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Appends locale pattern to the default (/) route.
 *
 * @package MultilingualRoutingBundle\Routing
 */
class RouteLoader extends Loader
{
    const LOCALE_REGEXP = '([a-z]{2}|[a-z]{2}_[A-Z]{2})';

    /** @var string */
    private $defaultLocale;

    /**
     * @param string $defaultLocale
     */
    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritdoc
     */
    public function load($resource, $type = null)
    {
        /** @var RouteCollection $collection */
        $collection = $this->resolve($resource)->load($resource);

        foreach ($collection as $name => $route) {
            if ($route->getPath() === '/') {
                $route->setPath('/{_locale}');
                $route->setDefault('_locale', $this->defaultLocale);
                $route->setRequirement('_locale', self::LOCALE_REGEXP);
            }
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function supports($resource, $type = null)
    {
        return $type === 'localized';
    }
}
