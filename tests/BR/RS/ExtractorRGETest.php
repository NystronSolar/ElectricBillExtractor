<?php

namespace App\Tests\BR\RS;

use App\Tests\CustomTestCase;
use NystronSolar\ElectricBillExtractor\BR\RS\BillRGE;
use NystronSolar\ElectricBillExtractor\BR\RS\ExtractorRGE;

class ExtractorRGETest extends CustomTestCase
{
    private bool $runTests;
    private array $pdfData;
    private ExtractorRGE $extractor;
    private BillRGE $bill;

    protected function setUp(): void
    {
        parent::setUp();

        $pdfFile = 'tests/content/BR/RS/RGE.pdf';
        $jsonFile = 'tests/content/BR/RS/RGE.json';

        $checks = $this->checkPDF($pdfFile) && $this->checkJSON($jsonFile);
        $this->assertBillJSON($jsonFile);

        if (!$checks) {
            $this->runTests = false;
            return;
        }

        $this->runTests = true;
        $this->pdfData = json_decode(file_get_contents($jsonFile), true);
        $this->extractor = new ExtractorRGE();
        $this->bill = $this->extractor->fromFile('tests/content/BR/RS/RGE.pdf');

        $this->assertIsObject($this->bill);
    }


    public function testExtractClient()
    {
        $billClient = $this->bill->getClient();
        $pdfClient = $this->pdfData["Client"];

        $this->assertSame($billClient->getName(), $pdfClient["Name"]);
        $this->assertSame($billClient->getAddress(), $pdfClient["Address"]);
        $this->assertSame($billClient->getDistrict(), $pdfClient["District"]);
        $this->assertSame($billClient->getCity(), $pdfClient["City"]);
    }

    public function testExtractBatch()
    {
        $billBatch = $this->bill->getBatch();
        $pdfBatch = $this->pdfData["Batch"];

        $this->assertSame($billBatch, $pdfBatch);
    }

    public function testExtractReadingGuide()
    {
        $billReadingGuide = $this->bill->getReadingGuide();
        $pdfReadingGuide = $this->pdfData["ReadingGuide"];

        $this->assertSame($billReadingGuide, $pdfReadingGuide);
    }

    protected function assertBillJSON(array |string $json)
    {
        if (is_string($json)) {
            $json = json_decode(file_get_contents($json), true);
        }

        $this->assertArrayHasKey('Client', $json, 'RGE Json File Don\'t Have Client Key.');

        $client = $json['Client'];
        $this->assertIsArray($client, 'RGE Json File Client Key Isn\'t an Array.');
        $this->assertArrayHasKey('Name', $client, 'RGE Json File Don\'t Have Client -> Name Key.');
        $this->assertArrayHasKey('Address', $client, 'RGE Json File Don\'t Have Client -> Address Key.');
        $this->assertArrayHasKey('District', $client, 'RGE Json File Don\'t Have Client -> District Key.');
        $this->assertArrayHasKey('City', $client, 'RGE Json File Don\'t Have Client -> City Key.');
        $this->assertArrayHasKey('Client', $json, 'RGE Json File Don\'t Have Client Key.');
    }
}