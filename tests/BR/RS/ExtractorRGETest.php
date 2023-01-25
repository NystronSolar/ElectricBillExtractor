<?php

namespace App\Tests\BR\RS;

use App\Tests\CustomTestCase;
use NystronSolar\ElectricBillExtractor\BR\RS\ExtractorRGE;

class ExtractorRGETest extends CustomTestCase
{
    public function testExtractClient()
    {
        $pdfFile = 'tests/content/BR/RS/RGE.pdf';
        $jsonFile = 'tests/content/BR/RS/RGE.json';

        $checks = $this->checkPDF($pdfFile) && $this->checkJSON($jsonFile);
        $this->assertBillJSON($jsonFile);

        if (!$checks) {
            return;
        }

        $data = json_decode(file_get_contents($jsonFile), true);

        $extractor = new ExtractorRGE();
        $bill = $extractor->fromFile('tests/content/BR/RS/RGE.pdf');

        $this->assertIsObject($bill);

        $billClient = $bill->getClient();
        $dataClient = $data["Client"];

        $this->assertSame($billClient->getName(), $dataClient["Name"]);
        $this->assertSame($billClient->getAddress(), $dataClient["Address"]);
        $this->assertSame($billClient->getDistrict(), $dataClient["District"]);
        $this->assertSame($billClient->getCity(), $dataClient["City"]);
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