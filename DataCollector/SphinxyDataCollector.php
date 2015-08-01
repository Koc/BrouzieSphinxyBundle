<?php

namespace Brouzie\Bundle\SphinxyBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

use Brouzie\Bundle\SphinxyBundle\Sphinxy\Registry;
use Brouzie\Sphinxy\Logging\DebugStack;

class SphinxyDataCollector extends DataCollector
{
    /**
     * @var DebugStack[]
     */
    private $loggers = array();

    private $connections;

    private $indexers;

    public function __construct(Registry $registry, array $indexers)
    {
        $this->connections = $registry->getConnectionNames();
        $this->indexers = $indexers;
    }

    /**
     * Adds the stack logger for a connection.
     *
     * @param string $name
     * @param DebugStack $logger
     */
    public function addLogger($name, DebugStack $logger)
    {
        $this->loggers[$name] = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $queries = array();
        foreach ($this->loggers as $name => $logger) {
            $queries[$name] = $this->sanitizeQueries($logger->queries);
        }

        $this->data = array(
            'queries' => $queries,
            'connections' => $this->connections,
            'indexers' => $this->indexers,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sphinxy';
    }

    public function getConnections()
    {
        return $this->data['connections'];
    }

    public function getIndexers()
    {
        return $this->data['indexers'];
    }

    public function getQueriesCount()
    {
        return array_sum(array_map('count', $this->data['queries']));
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $queries) {
            foreach ($queries as $query) {
                $time += $query['executionMS'];
            }
        }

        return $time;
    }

    private function sanitizeQueries($queries)
    {
        foreach ($queries as $i => $query) {
            $queries[$i] = $this->sanitizeQuery($query);
        }

        return $queries;
    }

    private function sanitizeQuery($query)
    {
        $query['params'] = (array)$query['params'];

        return $query;
    }
}
