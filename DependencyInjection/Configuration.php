<?php

namespace MultilingualRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('multilingual_routing');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('mapper')
                    ->defaultValue('postfix')
                    ->validate()
                        ->ifNotInArray(['postfix'])
                        ->thenInvalid('Currently supported mapper is: postfix')
                    ->end()
                ->end()
                ->arrayNode('map')
                    ->performNoDeepMerging()
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
