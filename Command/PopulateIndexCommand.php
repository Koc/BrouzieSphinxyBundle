<?php

namespace Brouzie\Bundle\SphinxyBundle\Command;

use Brouzie\Bundle\SphinxyBundle\Indexer\DoctrineQbIndexer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class PopulateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sphinxy:populate-index')
            ->addArgument('index', InputArgument::REQUIRED)
            ->addOption('truncate', null, InputOption::VALUE_NONE)
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $input->getArgument('index');

        $connecton = $this->getContainer()->get('sphinxy')->getConnection($input->getOption('connection'));
        $connecton->setLogger(null);

        $em = $this->getContainer()->get('doctrine')->getManager();
        /* @var $em EntityManager */

        $em->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $indexer = $this->getContainer()->get('sphinxy.indexer.doctrine_qb_indexer');
        /* @var $indexer DoctrineQbIndexer */

        if ($input->getOption('truncate')) {
            $output->writeln('<info>Truncate index</info>');
            $connecton->executeUpdate(sprintf('TRUNCATE RTINDEX %s', $connecton->getEscaper()->quoteIdentifier($index)));
        }

        list($min, $max) = $indexer->getRangeCriterias();

        $limit = 1000;
        $start = $min;
        do {
            $end = $start + $limit;
            $output->writeln(sprintf('Process records from %d to %d', $start, $end));

            $items = $indexer->getItemsByInterval($start, $end);
            $items = $indexer->processItems($items);
            $start = $end;

            if (!count($items)) {
                continue;
            }

            $insertQb = $connecton
                ->createQueryBuilder()
                ->replace($connecton->getEscaper()->quoteIdentifier($index));

            foreach ($items as $item) {
                $insertQb->addValues(
                    $connecton->getEscaper()->quoteSetArr(
                        $indexer->serializeItem($item)
                    )
                );
            }
            $insertQb->execute();

        } while ($start <= $max);
    }
}