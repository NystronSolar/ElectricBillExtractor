<?php

namespace NystronSolar\ElectricBillExtractorTests\TestCase;

use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Extractor;
use PHPUnit\Framework\TestCase;

class ExtractorTestCase extends TestCase
{
    /**
     * @param string                  $contentFolder  example: `V1RGE` - The name of the folder under tests/Content/bills and tests/Content/expected
     * @param class-string<Extractor> $extractorClass
     */
    public function assertByContentFolder(string $contentFolder, string $extractorClass): void
    {
        $billsFilesCount = count(glob(sprintf('tests/Content/bills/%s/*.txt', $contentFolder)));
        $expectedFilesCount = count(glob(sprintf('tests/Content/expected/%s/*.json', $contentFolder)));

        if ($billsFilesCount !== $expectedFilesCount) {
            throw new \Exception('Extractor Test Case Error: The count of files under '.sprintf('tests/Content/bills/%s/*.txt', $contentFolder).'  and '.sprintf('tests/Content/expected/%s/*.json', $contentFolder).' are different. Maybe a file is missing?');
        }

        for ($i = 1; $i <= $billsFilesCount; ++$i) {
            $billFileContents = file_get_contents(sprintf('tests/Content/bills/%s/%s.txt', $contentFolder, $i));
            $expectedFileContents = file_get_contents(sprintf('tests/Content/expected/%s/%s.json', $contentFolder, $i));

            if (false === $billFileContents || false === $expectedFileContents) {
                throw new \Exception('Extractor Test Case Error: All the files under '.sprintf('tests/Content/bills/%s/*.txt', $contentFolder).'  and '.sprintf('tests/Content/expected/%s/*.json', $contentFolder).' HAVE to be an increase number, starting in 1. Example: 1.txt, 2.txt | 1.json, 2.json');
            }

            $extractor = new $extractorClass($billFileContents);

            $this->assertEquals($this->jsonToBill((object) json_decode($expectedFileContents, false)), $extractor->extract());
        }
    }

    /**
     * @todo Convert the JSON To a Bill Object
     */
    public function jsonToBill(object $json): Bill|false
    {
        return false;
    }
}
