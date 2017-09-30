<?php

namespace GS\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gs_api');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('doctrine_table_prefix')
                    ->defaultValue('gs_api_')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
