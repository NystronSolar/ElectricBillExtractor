<?php

namespace App\Tests\BR\RS;

use App\Tests\CustomTestCase;
use DateTimeImmutable;
use DateTimeInterface;
use NystronSolar\ElectricBillExtractor\BR\RS\BillRGE;
use NystronSolar\ElectricBillExtractor\BR\RS\ExtractorRGE;

class ExtractorRGETest extends CustomTestCase
{
    private bool $runTests;
    private array $jsonData;
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
        $this->jsonData = json_decode(file_get_contents($jsonFile), true);
        $this->extractor = new ExtractorRGE();
        $this->bill = $this->extractor->fromFile($pdfFile);

        $this->assertIsObject($this->bill);
    }


    public function testExtractClient()
    {
        $billClient = $this->bill->getClient();
        $jsonClient = $this->jsonData["Client"];

        $this->assertSame($jsonClient["Name"], $billClient->getName());
        $this->assertSame($jsonClient["Address"], $billClient->getAddress());
        $this->assertSame($jsonClient["District"], $billClient->getDistrict());
        $this->assertSame($jsonClient["City"], $billClient->getCity());
    }

    public function testExtractBatch()
    {
        $billBatch = $this->bill->getBatch();
        $jsonBatch = $this->jsonData["Batch"];

        $this->assertSame($jsonBatch, $billBatch);
    }

    public function testExtractReadingGuide()
    {
        $billReadingGuide = $this->bill->getReadingGuide();
        $jsonReadingGuide = $this->jsonData["ReadingGuide"];

        $this->assertSame($jsonReadingGuide, $billReadingGuide);
    }

    public function testExtractPowerMeterId()
    {
        $billPowerMeterId = $this->bill->getPowerMeterId();
        $jsonPowerMeterId = $this->jsonData["PowerMeterId"];

        $this->assertSame($jsonPowerMeterId, $billPowerMeterId);
    }

    public function testExtractPages()
    {
        $billPages = $this->bill->getPages();
        $jsonPages = $this->jsonData["Pages"];

        $this->assertSame($jsonPages, $billPages);
    }

    public function testExtractDeliveryDate()
    {
        $billDeliveryDate = $this->bill->getDeliveryDate();
        $jsonDeliveryDate = DateTimeImmutable::createFromFormat("m/d/Y", $this->jsonData["DeliveryDate"]);

        $this->assertSameDate($jsonDeliveryDate, $billDeliveryDate);
    }

    public function testExtractNextReadingDate()
    {
        $billNextReadingDate = $this->bill->getNextReadingDate();
        $jsonNextReadingDate = DateTimeImmutable::createFromFormat("m/d/Y", $this->jsonData["NextReadingDate"]);

        $this->assertSameDate($jsonNextReadingDate, $billNextReadingDate);
    }

    public function testExtractDueDate()
    {
        $billDueDate = $this->bill->getDueDate();
        $jsonDueDate = DateTimeImmutable::createFromFormat("m/d/Y", $this->jsonData["DueDate"]);

        $this->assertSameDate($jsonDueDate, $billDueDate);
    }

    public function testExtractClassification()
    {
        $billClassification = $this->bill->getClassification();
        $jsonClassification = $this->jsonData["Classification"];

        $this->assertSame($jsonClassification, $billClassification);
    }

    public function testExtractSupplyType()
    {
        $billSupplyType = $this->bill->getSupplyType();
        $jsonSupplyType = $this->jsonData["SupplyType"];

        $this->assertSame($jsonSupplyType, $billSupplyType);
    }

    protected function assertSameDate(DateTimeInterface $expected, DateTimeInterface $actual)
    {
        $expectedDate = date_format($expected, "m/d/Y");
        $actualDate = date_format($actual, "m/d/Y");

        $this->assertSame($expectedDate, $actualDate);
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