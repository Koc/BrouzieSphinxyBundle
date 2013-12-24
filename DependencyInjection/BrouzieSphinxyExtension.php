<?php

namespace Brouzie\Bundle\SphinxyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BrouzieSphinxyExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('sphinxy.xml');

        $connections = array();
        foreach ($config['connections'] as $connection) {
            list($alias, $serviceId) = $this->loadConnection($connection, $container);
            $connections[$alias] = $serviceId;
        }

        $container->setParameter('sphinxy.connections', $connections);
        $container->setParameter('sphinxy.default_connection', $config['default_connection']);
    }

    /**
     * Loads a sphinxy connection
     *
     * @param array            $connection    A client configuration
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadConnection(array $connection, ContainerBuilder $container)
    {
        $dsnParameterId = sprintf('sphinxy.connection.%s_dsn', $connection['alias']);
        $container->setParameter($dsnParameterId, $connection['dsn']);

        $loggingParameterId = sprintf('sphinxy.connection.%s_logging', $connection['alias']);
        $container->setParameter($loggingParameterId, $connection['logging']);

        $adapterConnectionId = sprintf('sphinxy.%s_adapter_connection', $connection['alias']);
        $adapterConnectionDef = new Definition('Brouzie\Sphinxy\Connection\PdoConnection');
        $adapterConnectionDef->addArgument($container->getParameter($dsnParameterId));
        $adapterConnectionDef->setPublic(false);
        $container->setDefinition($adapterConnectionId, $adapterConnectionDef);

        $connectionId = sprintf('sphinxy.%s_connection', $connection['alias']);
        $connectionDef = new Definition('Brouzie\Sphinxy\Connection');
        $connectionDef->addArgument(new Reference($adapterConnectionId));

        $loggerId = null;
        if ($connection['logging']) {
            $loggerId = sprintf('sphinxy.%s_logger', $connection['alias']);
            $container->setDefinition($loggerId, new DefinitionDecorator('sphinxy.logger'));
        }

        if ($connection['profiling']) {
            $profilingLoggerId = sprintf('sphinxy.%s_logger_profiling', $connection['alias']);
            $container->setDefinition($profilingLoggerId, new DefinitionDecorator('sphinxy.logger_profiling'));
            $profilingLogger = new Reference($profilingLoggerId);
            $container->getDefinition('data_collector.sphinxy')->addMethodCall('addLogger', array($connection['alias'], $profilingLogger));

            if (null !== $loggerId) {
                $chainLogger = new DefinitionDecorator('sphinxy.logger_chain');
                $chainLogger->addMethodCall('addLogger', array($profilingLogger));

                $loggerId = sprintf('sphinxy.%s_logger_chain', $connection['alias']);
                $container->setDefinition($loggerId, $chainLogger);
            }
        }

        if ($loggerId) {
            $connectionDef->addMethodCall('setLogger', array(new Reference($loggerId)));
        }

        $container->setDefinition($connectionId, $connectionDef);

        return array($connection['alias'], $connectionId);
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }
}
