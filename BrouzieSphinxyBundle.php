<?php

namespace Brouzie\Bundle\SphinxyBundle;

use Brouzie\Bundle\SphinxyBundle\DependencyInjection\Compiler\RegisterIndexersPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BrouzieSphinxyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterIndexersPass());
    }
}
