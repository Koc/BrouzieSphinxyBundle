<?php

namespace Brouzie\Bundle\SphinxyBundle\Sphinxy;

use Brouzie\Sphinxy\Registry as BaseRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Registry extends BaseRegistry implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnection($name = null)
    {
        return $this->container->get(parent::getConnection($name));
    }

    /**
     * {@inheritDoc}
     */
    public function getConnections()
    {
        $connections = array();
        foreach (parent::getConnections() as $name => $serviceId) {
            $connections[$name] = $this->container->get($serviceId);
        }

        return $connections;
    }
}
