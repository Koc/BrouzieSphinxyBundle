<?php

namespace Brouzie\Bundle\SphinxyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            ->addOption('batch-size', null, InputOption::VALUE_OPTIONAL, '', 1000);
        //            ->addOption('connection', null, InputOption::VALUE_OPTIONAL)
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $index = $input->getArgument('index');
        $indexManager = $this->getContainer()->get('sphinxy.index_manager');

        if ($input->getOption('truncate')) {
            $io->text(sprintf('<info>[%s]</info> Truncate <info>%s</info> index ', date('Y-m-d H:i:s'), $index));
            $indexManager->truncate($index);
        }

        $progressBar = new ProgressBar($io);
        $progressBar->setBarWidth(60);

        $rangeCriterias = array();
        if ($input->getOption('increment')) {
            if ($indexRange = $indexManager->getIndexRange($index)) {
                $rangeCriterias = array('min' => $indexRange['max']);
            }
        }

        $batchCallback = function ($info) use ($progressBar) {
            if (!$progressBar->getMaxSteps()) {
                $progressBar->start($info['max_id']);
            }

            $progressBar->setProgress($info['id_from']);
        };

        $io->text(sprintf('<info>[%s]</info> Populate <info>%s</info> index ', date('Y-m-d H:i:s'), $index));

        $indexManager->reindex($index, $input->getOption('batch-size'), $batchCallback, $rangeCriterias);

        $progressBar->finish();

        $io->success('Index was successful populated.');
    }
}
