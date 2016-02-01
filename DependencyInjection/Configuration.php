<?php

namespace PRayno\CasAuthBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('p_rayno_cas_auth');

        $rootNode
            ->children()
                ->scalarNode('server_login_url')->end()
                ->scalarNode('server_validation_url')->end()
                ->scalarNode('server_logout_url')->end()
                ->scalarNode('xml_namespace')
                    ->defaultValue('cas')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
