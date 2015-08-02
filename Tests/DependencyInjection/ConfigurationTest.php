<?php

namespace Brouzie\Bundle\SphinxyBundle\Tests\DependencyInjection;

use Brouzie\Bundle\SphinxyBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEmptyConfigLoad()
    {
        $this->processConfiguration(new Configuration(false), array());
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param array $configs
     *
     * @return array
     */
    protected function processConfiguration(ConfigurationInterface $configuration, array $configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}
