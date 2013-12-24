<?php

namespace Brouzie\Bundle\SphinxyBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SphinxyDataCollector extends DataCollector
{
    /**
     * {@inheritDoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $queries = array();
        foreach ($this->loggers as $name => $logger) {
            $queries[$name] = $this->sanitizeQueries($name, $logger->queries);
        }

        $this->data = array(
            'queries'     => $queries,
            'connections' => $this->connections,
            'managers'    => $this->managers,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sphinxy';
    }
}
