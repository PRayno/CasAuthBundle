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
        $treeBuilder = new TreeBuilder('p_rayno_cas_auth');
        //fix deprecated call
        if(\Symfony\Component\HttpKernel\Kernel::VERSION > 4.2)
            $rootNode = $treeBuilder->getRootNode();
        else
            $rootNode = $treeBuilder->root('p_rayno_cas_auth');

        $rootNode
            ->children()
            ->scalarNode('server_login_url')->end()
            ->scalarNode('server_validation_url')->end()
            ->scalarNode('server_logout_url')->end()
            ->scalarNode('xml_namespace')
            ->defaultValue('cas')
            ->end()
            ->arrayNode('options')
            ->prototype('scalar')->end()
            ->defaultValue(array())
            ->end()
            ->scalarNode('username_attribute')
            ->defaultValue('user')
            ->end()
            ->scalarNode('query_ticket_parameter')
            ->defaultValue('ticket')
            ->end()
            ->scalarNode('query_service_parameter')
            ->defaultValue('service')
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
