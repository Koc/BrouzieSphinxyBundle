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
    private $debug;

    public function __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }

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
                ->scalarNode('default_connection')->cannotBeEmpty()->defaultValue('default')->end()
                ->arrayNode('connections')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('alias', false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('alias')->isRequired()->end()
                            ->booleanNode('logging')->defaultValue($this->debug)->end()
                            ->booleanNode('profiling')->defaultValue($this->debug)->end()
                            ->scalarNode('dsn')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
