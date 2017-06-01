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
        $sourceFile   = $input->getArgument('file');
        $linesPerFile = $input->getOption('lines') ? $input->getOption('lines') : 250000;

        // Sort the file alphabetically to group quads related to the same subject.
        exec(escapeshellcmd('sort ' . $sourceFile));

        $file = fopen($sourceFile, "r");

        if ($file) {
            $currentLine         = 1;
            $currentFile         = 0;
            $currentFileContents = '';
            $currentSubject      = '';
            $linesPerFileReached = false;

            while ($line = fgets($file)) {
                if ($currentLine % $linesPerFile === 0) {
                    $linesPerFileReached = true;
                }

                // If the line count has been reached and all triples related to the current subject have been handled.
                if ($linesPerFileReached && $currentSubject !== explode(' ', $line)[0]) {
                    // Write the contents to a new suffixed file.
                    file_put_contents(dirname($sourceFile) . DIRECTORY_SEPARATOR . basename($sourceFile) . str_pad($currentFile,
                            4, '0', STR_PAD_LEFT), $currentFileContents);
                    $currentFileContents = '';
                    $currentFile++;
                    $linesPerFileReached = false;
                }

                $currentFileContents .= $line;

                $currentSubject = explode(' ', $line)[0];
                $currentLine++;
            }

            file_put_contents(
                dirname($sourceFile) . DIRECTORY_SEPARATOR . basename($sourceFile) . str_pad($currentFile,
                    4, '0', STR_PAD_LEFT), $currentFileContents);

            fclose($file);
        } else {
            $output->writeln('<error>The file ' . $sourceFile . ' could not be opened.</error>');
        }

        $output->writeln('<info>The file ' . $sourceFile . ' has been split.</info>');
    }

}
