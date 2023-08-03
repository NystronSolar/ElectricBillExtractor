<?php

namespace NystronSolar\ElectricBillExtractorTests\Scripts;

use Smalot\PdfParser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class UploadBillCommand extends Command
{
    public array $availableExtractors = ['V1RGE', 'V2RGE', 'V3RGE'];

    public string $contentFolder = '/tests/Content';

    private SymfonyStyle $io;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $billFilename = $this->getBillFilename();
        if (!$billFilename) {
            return Command::FAILURE;
        }

        $text = $this->parseFile($billFilename);
        if (!$text) {
            return Command::FAILURE;
        }

        $extractor = $this->getExtractor();
        $writeResult = $this->writeToNextId($extractor, $text);
        if (!$writeResult) {
            return Command::FAILURE;
        }

        $this->io->success('Bill Uploaded: Text and JSON file created.');
        $this->io->note('What\'s Next?');
        $this->io->listing([
            'Remove all secrets from Bill Text File',
            'Update the new JSON file with the correct data',
        ]);

        return Command::SUCCESS;
    }

    private function getExtractor(): string
    {
        $io = $this->io;

        return (string) $io->choice('Extractor', $this->availableExtractors);
    }

    private function getBillFilename(): false|string
    {
        $io = $this->io;

        $billFilenameRaw = (string) $io->askQuestion(new Question('The Bill Filename (Under the current working directory - "./")'));
        $billFilename = sprintf('%s/%s', getcwd(), $billFilenameRaw);
        if (!file_exists($billFilename)) {
            $io->error(sprintf('No such file in %s', $billFilename));

            return false;
        }

        return $billFilename;
    }

    private function parseFile(string $filename): false|string
    {
        $io = $this->io;
        try {
            $parser = new Parser();
            $text = $parser->parseFile($filename)->getText();
        } catch (\Exception $e) {
            $io->error(sprintf('Parser error: %s', $e->getMessage()));

            return false;
        }

        return $text;
    }

    private function findNextId(string $extractor): int
    {
        $files = glob(
            sprintf(
                '%s/%s/%s/%s/*.txt',
                getcwd(),
                $this->contentFolder,
                'bills',
                $extractor
            )
        );
        $files = !empty($files) ? $files : ['0.txt'];
        $lastId = (int) basename($files[array_key_last($files)], '.txt');

        return $lastId + 1;
    }

    private function writeToNextId(string $extractor, string $text): bool
    {
        $io = $this->io;

        $currentId = $this->findNextId($extractor);
        $billFile = sprintf('%s/%s/%s/%s/%s.txt', getcwd(), $this->contentFolder, 'bills', $extractor, $currentId);
        $billResult = file_put_contents($billFile, $text);

        if (!$billResult) {
            $io->error(sprintf('Bill file %s already exists!', $billFile));

            return false;
        }

        $jsonSkeletonFile = sprintf('%s/%s/%s', getcwd(), $this->contentFolder, 'expected/skeleton.json');
        $jsonFile = sprintf('%s/%s/%s/%s/%s.json', getcwd(), $this->contentFolder, 'expected', $extractor, $currentId);
        $jsonResult = copy(
            $jsonSkeletonFile,
            $jsonFile
        );

        if (!$jsonResult) {
            $io->error(sprintf('json file %s already exists or skeleton in %s don\' exists!!', $jsonFile, $jsonSkeletonFile));

            return false;
        }

        return true;
    }
}
