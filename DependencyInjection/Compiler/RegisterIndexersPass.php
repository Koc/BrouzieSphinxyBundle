<?php

namespace Brouzie\Bundle\SphinxyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterIndexersPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $indexesToService = array();
        foreach ($container->findTaggedServiceIds('sphinxy.indexer') as $id => $attr) {
            $index = $attr[0]['index'];
            $indexesToService[$index] = $id;
        }
        $container->setParameter('sphinxy.indexers', $indexesToService);
    }
}
