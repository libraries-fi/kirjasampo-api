<?php

namespace SahaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SahaDumpIndexCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('saha:dump:index')
            ->setDescription('Index files created with saha:dump:convert to Elasticsearch')
            ->addArgument('file', InputArgument::OPTIONAL, 'The JSON-LD file to index without the file number suffix');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        $fileNumber = 0;

        // The files were numbered by the split command using a suffix of 4 zeroes, so we apply the same suffix here.
        while (file_exists($file . str_pad($fileNumber, 4, '0', STR_PAD_LEFT))) {
            exec(escapeshellcmd('curl -XPOST "http://localhost:9200/_bulk" --data-binary @' . $file . str_pad($fileNumber,
                    4, '0', STR_PAD_LEFT)));

            $fileNumber++;
        }

        $output->writeln('<info>' . $fileNumber . ' files have been indexed.</info>');
    }

}
