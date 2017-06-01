<?php

namespace SahaBundle\Command;

use ML\JsonLD\JsonLD;
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
            ->setDescription('Convert files created with saha:dump:split into JSON')
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

        $triplesCount = 0;
        $fileNumber   = 0;

        // Add a left padding of 4 zeroes required by the split command.
        while (file_exists($sourceFile . str_pad($fileNumber, 4, '0', STR_PAD_LEFT))) {
            $output->writeln('file: ' . $sourceFile . str_pad($fileNumber, 4, '0', STR_PAD_LEFT));
            $fileSuffix = str_pad($fileNumber, 4, '0', STR_PAD_LEFT);
            $data       = new EasyRdf_Graph();
            $data->parseFile($sourceFile . $fileSuffix, 'ntriples');

            $triplesCount += $data->countTriples();

            $data = json_decode($data->serialise('jsonld'), true);

            foreach ($data as $graph) {
                $line = json_encode([
                    'index' => [
                        '_index' => 'books',
                        '_type'  => 'book',
                    ],
                ]);

                // Separate the action and document with a new line.
                $line .= PHP_EOL;

                $line .= JsonLD::toString($graph);

                // End the current document with a new line.
                $line .= PHP_EOL;

                file_put_contents($targetFile . $fileSuffix, $line, FILE_APPEND);
            }

            $fileNumber++;
        }

        $output->writeln('<info>' . $triplesCount . ' triples were converted.</info>');
    }
}
