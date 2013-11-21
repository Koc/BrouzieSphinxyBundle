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
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        foreach ($config['connections'] as $connection) {
            $this->loadConnection($connection, $container);
        }
    }

    /**
     * Loads a sphinxy connection
     *
     * @param array            $connection    A client configuration
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadConnection(array $connection, ContainerBuilder $container)
    {
        $dsnParameterId = sprintf('brouzie_sphinxy.connection.%s_dsn', $connection['alias']);
        $container->setParameter($dsnParameterId, $connection['dsn']);

        $loggingParameterId = sprintf('brouzie_sphinxy.connection.%s_logging', $connection['alias']);
        $container->setParameter($loggingParameterId, $connection['logging']);

        $adapterConnectionId = sprintf('brouzie_sphinxy.%s_adapter_connection', $connection['alias']);
        $adapterConnectionDef = new Definition('Brouzie\Sphinxy\Connection\PdoConnection');
        $adapterConnectionDef->addArgument($container->getParameter($dsnParameterId));
        $adapterConnectionDef->setPublic(false);
        $container->setDefinition($adapterConnectionId, $adapterConnectionDef);

        $connectionId = sprintf('brouzie_sphinxy.%s_connection', $connection['alias']);
        $connectionDef = new Definition('Brouzie\Sphinxy\Connection');
        $connectionDef->addArgument(new Reference($adapterConnectionId));

        if ($connection['logging']) { //FIXME: this will not work correctly for references like %kernel.debug%
            // https://github.com/snc/SncRedisBundle/issues/114
            $profilingLoggerId = sprintf('brouzie_sphinxy.%s_logger', $connection['alias']);
            $container->setDefinition($profilingLoggerId, new DefinitionDecorator('brouzie.sphinxy.logger'));
            $connectionDef->addMethodCall('setLogger', array(new Reference($profilingLoggerId)));
        }
        $container->setDefinition($connectionId, $connectionDef);
    }
}
