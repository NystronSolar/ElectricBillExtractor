<?php

namespace App\Tests\BR\RS;

use App\Tests\CustomTestCase;
use DateTimeImmutable;
use NystronSolar\ElectricBillExtractor\BR\RS\BillRGE;
use NystronSolar\ElectricBillExtractor\BR\RS\ExtractorRGE;

class ExtractorRGETest extends CustomTestCase
{
    private array $jsonData;
    private ExtractorRGE $extractor;
    private BillRGE $bill;

    protected static function getPaths(): array
    {
        return [
            'json' => 'tests/content/BR/RS/RGE.json',
            'pdf' => 'tests/content/BR/RS/RGE.pdf'
        ];
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $pdfFile = static::getPaths()['pdf'];
        $jsonFile = static::getPaths()['json'];
        $jsonContent = file_get_contents($jsonFile);

        static::assertPDF($pdfFile);
        static::assertJson($jsonContent);
        static::assertBillJSON($jsonFile);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $pdfFile = static::getPaths()['pdf'];
        $jsonFile = static::getPaths()['json'];

        $this->jsonData = json_decode(file_get_contents($jsonFile), true);
        $this->extractor = new ExtractorRGE();
        $this->bill = $this->extractor->fromFile($pdfFile);

        $this->assertNotFalse($this->bill);
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
        $billClassification = $this->bill->getClient()->getBuilding()->getClassification();
        $jsonClassification = $this->jsonData["Classification"];

        $this->assertSame($jsonClassification, $billClassification);
    }

    public function testExtractSupplyType()
    {
        $billSupplyType = $this->bill->getClient()->getBuilding()->getSupplyType();
        $jsonSupplyType = $this->jsonData["SupplyType"];

        $this->assertSame($jsonSupplyType, $billSupplyType);
    }

    public function testExtractVoltage()
    {
        $billVoltage = $this->bill->getClient()->getBuilding()->getVoltage();
        $billVoltage = $this->bill->getClient()->getBuilding()->getVoltage();
        $jsonVoltage = $this->jsonData["Voltage"];

        $this->assertSame($jsonVoltage, $billVoltage);
    }

    public static function assertBillJSON(array |string $json): void
    {
        if (is_string($json)) {
            $json = json_decode(file_get_contents($json), true);
        }

        $billKeys = ['Client', 'Batch', 'ReadingGuide', 'PowerMeterId', 'Pages', 'DeliveryDate', 'NextReadingDate', 'DueDate', 'Classification', 'SupplyType', 'Voltage'];
        static::assertIsArray($json);
        static::assertArrayHasKeys($billKeys, $json, 'RGE Json File Don\'t Have %s Key.');

        $client = $json['Client'];
        $clientKeys = ['Name', 'Address', 'District', 'City',];
        static::assertIsArray($client, 'RGE Json File Client Key Isn\'t an Array.');
        static::assertArrayHasKeys($clientKeys, $client, 'RGE Json File Don\'t Have Client -> %s Key.');

        $pages = $json['Pages'];
        $pagesKey = ['Actual', 'Total'];
        static::assertIsArray($pages);
        static::assertArrayHasKeys($pagesKey, $pages, 'RGE Json File Don\'t Have Pages -> %s Key.');
    }
}