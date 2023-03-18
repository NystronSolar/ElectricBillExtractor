<?php

namespace App\Tests\BR\RS;

use App\Tests\CustomTestCase;
use NystronSolar\ElectricBillExtractor\BR\RS\ExtractorRGE;

class ExtractorRGETest extends CustomTestCase
{
    private ExtractorRGE $extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = $this->extractor ?? new ExtractorRGE();
    }

    public static function readJson(string $path, bool $datesToObject = true): array|null
    {
        $jsonFile = $path;
        $jsonContent = file_get_contents($jsonFile);
        $json = json_decode($jsonContent, true);

        if ($datesToObject) {
            foreach ($json as $key => $value) {
                if (is_string($value) && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value)) {
                    $json[$key] = \DateTime::createFromFormat('m/d/Y', $value);
                }
            }
        }

        return $json;
    }

    public static function readPDF(string $path, bool $moneyToFloat = true): array
    {
        $extractor = new ExtractorRGE();
        $billFile = $path;
        $bill = $extractor->fromFile($billFile);

        if($moneyToFloat) {
            $bill['Cost'] = (int) $bill['Cost']->getAmount();
        }

        return $bill;
    }


    public static function appProvider(): array
    {
        return [
            [
                'expected' => static::readJson('tests/content/BR/RS/RGE.json'),
                'actual' => static::readPDF('tests/content/BR/RS/RGE.pdf'),
            ],
            [
                'expected' => static::readJson('tests/content/BR/RS/RGENoPrice.json'),
                'actual' => static::readPDF('tests/content/BR/RS/RGENoPrice.pdf'),
            ],
        ];
    }

    /**
     * @dataProvider appProvider
     */
    public function testExtractMainArrays(array $expected, array $actual)
    {
        $this->assertSame($expected['Client'], $actual['Client']);
        $this->assertSame($expected['Pages'], $actual['Pages']);
        $this->assertSame($expected['Notices'], $actual['Notices']);
        $this->assertSame($expected['SolarGeneration'], $actual['SolarGeneration']);
        $this->assertSame($expected['EnergyData'], $actual['EnergyData']);
    }

    /**
     * @dataProvider appProvider
     */
    public function testExtractMainData(array $expected, array $actual): void
    {
        $this->assertSame($expected['Batch'], $actual['Batch']);
        $this->assertSame($expected['ReadingGuide'], $actual['ReadingGuide']);
        $this->assertSame($expected['PowerMeterId'], $actual['PowerMeterId']);
        $this->assertSame($expected['InstallationCode'], $actual['InstallationCode']);
        $this->assertSame($expected['Cost'], $actual['Cost']);
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
        $this->assertSame($expected['TotalDays'], $actual['TotalDays']);
    }
}