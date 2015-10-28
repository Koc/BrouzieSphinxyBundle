<?php

namespace Brouzie\Bundle\SphinxyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        //TODO: add support of the connection

        $this
            ->setName('sphinxy:populate-index')
            ->addArgument('index', InputArgument::REQUIRED)
            ->addOption('truncate', null, InputOption::VALUE_NONE)
            ->addOption('increment', null, InputOption::VALUE_NONE)
            ->addOption('batch-size', null, InputOption::VALUE_OPTIONAL, '', 1000)
//            ->addOption('connection', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $input->getArgument('index');
        $indexManager = $this->getContainer()->get('sphinxy.index_manager');

        if ($input->getOption('truncate')) {
            $output->writeln('<info>Truncate index</info>');
            $indexManager->truncate($index);
        }

        $progress = $this->getHelperSet()->get('progress');
        /* @var $progress ProgressHelper  */
        $progress->setBarWidth(50);
        //FIXME: fix percentage calculation for last iteration

        $rangeCriterias = array();
        if ($input->getOption('increment')) {
            if ($indexRange = $indexManager->getIndexRange($index)) {
                $rangeCriterias = array('min' => $indexRange['max']);
            }
        }

        $batchCallback = function ($info) use ($output, $progress) {
            static $started = false;
            if (!$started) {
                $progress->start($output, $info['max_id']);
                $started = true;
            }

            $progress->setCurrent($info['id_from'], true);
        };
        $indexManager->reindex($index, $input->getOption('batch-size'), $batchCallback, $rangeCriterias);

        $progress->finish();
    }
}
