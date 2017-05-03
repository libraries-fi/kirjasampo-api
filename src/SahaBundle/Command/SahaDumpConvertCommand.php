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

        $triplesCount = $data->countTriples();

        $data = $data->toRdfPhp();

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        $line = '';

        foreach ($data as $subject => $predicateArray) {
            foreach ($predicateArray as $predicate => $objectArray) {
                $line = json_encode([
                    'index' => [
                        '_index' => 'books',
                        '_type'  => 'book',
                    ],
                ]);

                // Separate the action and document with a new line.
                $line .= PHP_EOL;

                $triple = [
                    'subject'   => $subject,
                    'predicate' => $predicate,
                    'object'    => $objectArray[0]['value'],
                ];

                if (isset($objectArray[0]['lang'])) {
                    $triple['lang'] = $objectArray[0]['lang'];
                }

                $line .= json_encode($triple);
            }

            // End the current document with a new line.
            $line .= PHP_EOL;

            file_put_contents($targetFile, $line, FILE_APPEND);
        }

        $output->writeln('<info>The output JSON file has been written to ' . $targetFile . '.</info>');
        $output->writeln('<info>' . $triplesCount . ' triples were converted.</info>');
    }
}
