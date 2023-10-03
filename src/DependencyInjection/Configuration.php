<?php

namespace ATSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('at_search');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()

                ->arrayNode('search')
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('client')->end()
                        ->booleanNode('enable_update_events')->defaultFalse()->end()
                        ->arrayNode('mappings')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('namespace')->end()
                                ->scalarNode('dir')->end()
                            ->end()
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}