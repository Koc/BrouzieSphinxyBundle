<?php

namespace Brouzie\Bundle\SphinxyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * SphinxyBundle configuration class.
 *
 * @author Konstantin.Myakshin <koc-dp@yandex.ru>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('brouzie_sphinxy');

        $this->addConnectionsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds the brouzie_sphinxy.connections configuration
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addConnectionsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('connection')
            ->children()
                ->arrayNode('connections')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('alias', false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('alias')->isRequired()->end()
                            ->booleanNode('logging')->defaultValue('%kernel.debug%')->end()
                            ->scalarNode('dsn')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
