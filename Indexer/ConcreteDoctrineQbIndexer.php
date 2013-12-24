<?php

namespace Brouzie\Bundle\SphinxyBundle\Indexer;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

abstract class ConcreteDoctrineQbIndexer
{
    /**
     * @param EntityManager $em
     *
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder(EntityManager $em);

    public function getRootIdentifier()
    {
        return 'e.id';
    }

    public function processItems(array $items, EntityManager $em)
    {
        return $items;
    }

    public function serializeItem($item)
    {
        return $item;
    }

    public function getHydrationMode()
    {
        return AbstractQuery::HYDRATE_ARRAY;
    }
}
