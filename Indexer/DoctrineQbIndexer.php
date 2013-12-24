<?php

namespace Brouzie\Bundle\SphinxyBundle\Indexer;

use Doctrine\ORM\EntityManager;

class DoctrineQbIndexer
{
    /**
     * @var ConcreteDoctrineQbIndexer
     */
    private $concreteIndexer;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em, ConcreteDoctrineQbIndexer $indexer)
    {
        $this->em = $em;
        $this->concreteIndexer = $indexer;
    }

    public function getRangeCriterias()
    {
        //TODO: не работает select('MIN(p.id) AS min, MAX(p.id) AS max')
        $rootId = $this->concreteIndexer->getRootIdentifier();

        $min = $this->concreteIndexer->getQueryBuilder($this->em)
            ->select($rootId)
            ->orderBy($rootId, 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleScalarResult()
        ;

        $max = $this->concreteIndexer->getQueryBuilder($this->em)
            ->select($rootId)
            ->orderBy($rootId, 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleScalarResult()
        ;

        return array($min, $max);
    }

    public function getItemsByIds(array $ids)
    {
        $rootId = $this->concreteIndexer->getRootIdentifier();

        $items = $this->concreteIndexer->getQueryBuilder($this->em)
            ->andWhere(sprintf('%s IN (:ids)', $rootId))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult($this->concreteIndexer->getHydrationMode())
        ;

        return $items;
    }

    public function getItemsByInterval($idFrom, $idTo)
    {
        $rootId = $this->concreteIndexer->getRootIdentifier();

        $items = $this->concreteIndexer->getQueryBuilder($this->em)
            ->andWhere(sprintf('%s >= :min AND %s < :max', $rootId, $rootId))
            ->setParameter('min', $idFrom)
            ->setParameter('max', $idTo)
            ->getQuery()
            ->getResult($this->concreteIndexer->getHydrationMode())
        ;

        return $items;
    }

    public function processItems(array $items)
    {
        return $this->concreteIndexer->processItems($items, $this->em);
    }

    public function serializeItem($item)
    {
        return $this->concreteIndexer->serializeItem($item);
    }
}
