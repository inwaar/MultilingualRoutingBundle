<?php

namespace MultilingualRoutingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class MultilingualRoutingExtension extends ConfigurableExtension implements CompilerPassInterface
{
    const HOST_LOCALE_MAPPER_SERVICE_ID = 'multilingual.routing.mapper.%s';
    const ROUTER_SERVICE_ID = 'multilingual.routing.router';

    /**
     * @inheritdoc
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $mapperDefinition = $this->processMapper($container, $config);
        $this->processRouter($container, $mapperDefinition);
    }


    /**
     * @param ContainerBuilder $container
     * @param $mapperDefinition
     */
    protected function processRouter(ContainerBuilder $container, Definition $mapperDefinition)
    {
        $definition = $container->getDefinition(self::ROUTER_SERVICE_ID);
        $definition->addArgument($mapperDefinition);
    }

    protected function processMapper(ContainerBuilder $container, $config): Definition
    {
        $definition = $container->getDefinition(sprintf(self::HOST_LOCALE_MAPPER_SERVICE_ID, $config['mapper']));
        $definition->addArgument($config['map']);
        return $definition;
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return 'multilingual_routing';
    }
}
