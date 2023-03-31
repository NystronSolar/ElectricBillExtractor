<?php

namespace App\Tests\BR\RS;

use App\Tests\ExtractorTestCase;
use NystronSolar\ElectricBillExtractor\BR\RS\ExtractorOldRGE;
use NystronSolar\ElectricBillExtractor\Extractor;

class ExtractorOldRGETest extends ExtractorTestCase
{
    protected function generateExtractor(): Extractor
    {
        $this->extractor = new ExtractorOldRGE();

        return $this->extractor;
    }

    public function readPDF(string $path, bool $moneyToFloat = true): array
    {
        $bill = parent::readPDF($path);

        if ($moneyToFloat) {
            $bill['Cost'] = (int) $bill['Cost']->getAmount();
        }

        return $bill;
    }

    public function appProvider(): array
    {
        return $this->generateProvider('BR', 'RS', 'OldRGE');
    }

    /**
     * @dataProvider appProvider
     */
    public function testExtractMainArrays(array $expected, array $actual)
    {
        $this->assertArraysValuesSame('Client', $expected, $actual);
        $this->assertArraysValuesSame('Pages', $expected, $actual);
        $this->assertArraysValuesSame('Notices', $expected, $actual);
        $this->assertArraysValuesSame('SolarGeneration', $expected, $actual);
        $this->assertArraysValuesSame('EnergyData', $expected, $actual);
    }

    /**
     * @dataProvider appProvider
     */
    public function testExtractMainData(array $expected, array $actual): void
    {
        $this->assertArraysValuesSame('Batch', $expected, $actual);
        $this->assertArraysValuesSame('ReadingGuide', $expected, $actual);
        $this->assertArraysValuesSame('PowerMeterId', $expected, $actual);
        $this->assertArraysValuesSame('InstallationCode', $expected, $actual);
        $this->assertArraysValuesSame('Cost', $expected, $actual);
        $this->assertArraysValuesSame('PN', $expected, $actual);
    }

    /**
     * @dataProvider appProvider
     */
    public function testExtractDates(array $expected, array $actual): void
    {
        $this->assertSameDate($expected['Date'], $actual['Date']);
        $this->assertSameDate($expected['DeliveryDate'], $actual['DeliveryDate']);
        $this->assertSameDate($expected['NextReadingDate'], $actual['NextReadingDate']);
        $this->assertSameDate($expected['DueDate'], $actual['DueDate']);
        $this->assertSameDate($expected['ActualReadingDate'], $actual['ActualReadingDate']);
        $this->assertSameDate($expected['PreviousReadingDate'], $actual['PreviousReadingDate']);





        // $this->assertSameDate($expected['DeliveryDate'], $actual['DeliveryDate']);
        // $this->assertSameDate($expected['NextReadingDate'], $actual['NextReadingDate']);
        // $this->assertSameDate($expected['ActualReadingDate'], $actual['ActualReadingDate']);
    }
}