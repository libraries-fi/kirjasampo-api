<?php

namespace SahaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use EasyRdf_Graph;

class SahaDumpConvertCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('saha:dump:convert')
            ->setDescription('Convert a Saha dump file to JSON')
            ->addArgument('file', InputArgument::REQUIRED, 'A path to the .nq dump file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceFile = $input->getArgument('file');
        $targetFile = dirname($sourceFile) . DIRECTORY_SEPARATOR . basename($sourceFile, '.nq') . '.json';

        if ( ! is_writable(dirname($targetFile))) {
            $output->writeln(
                '<comment>The target directory ' . dirname($targetFile) . ' is not writable.
                Writing to system temporary directory instead.</comment>'
            );

            $targetFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($sourceFile, '.nq') . '.json';
        }

        $data = new EasyRdf_Graph();
        $data->parseFile($sourceFile, 'ntriples');

        $data = $data->serialise('json');

        file_put_contents($targetFile, $data);

        $output->writeln('<info>The output JSON file has been written to ' . $targetFile . '.</info>');
    }
}
