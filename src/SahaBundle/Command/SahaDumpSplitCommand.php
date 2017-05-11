<?php

namespace SahaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SahaDumpSplitCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('saha:dump:split')
            ->setDescription('Split a Saha dump into smaller files to reduce resource usage')
            ->addArgument('file', InputArgument::REQUIRED, 'A path to the .nq dump file')
            ->addOption('lines', 'l', InputOption::VALUE_REQUIRED, 'Lines per file (default 250,000)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceFile = $input->getArgument('file');

        $linesPerFile = $input->getOption('lines') ? $input->getOption('lines') : 250000;

        chdir(dirname($sourceFile));
        exec(escapeshellcmd('split -d -a 4 -l ' . $linesPerFile . ' ' . $sourceFile . ' ' . basename($sourceFile)));

        $output->writeln('<info>The file ' . $sourceFile . ' has been split.</info>');
    }

}
