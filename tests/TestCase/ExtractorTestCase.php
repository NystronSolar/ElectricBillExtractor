<?php

namespace NystronSolar\ElectricBillExtractorTests\TestCase;

use NystronSolar\ElectricBillExtractor\Entity\Address;
use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Entity\Client;
use NystronSolar\ElectricBillExtractor\Entity\Dates;
use NystronSolar\ElectricBillExtractor\Entity\Debit;
use NystronSolar\ElectricBillExtractor\Entity\Debits;
use NystronSolar\ElectricBillExtractor\Entity\Establishment;
use NystronSolar\ElectricBillExtractor\Entity\Power;
use NystronSolar\ElectricBillExtractor\Entity\Powers;
use NystronSolar\ElectricBillExtractor\Entity\SolarGeneration;
use NystronSolar\ElectricBillExtractor\Extractor;
use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;
use PHPUnit\Framework\TestCase;

class ExtractorTestCase extends TestCase
{
    final public function assertEqualsDate(\DateTimeInterface $expected, \DateTimeInterface $actual, string $message = '', string $format = 'd/m/Y'): void
    {
        $this->assertEquals($expected->format($format), $actual->format($format), $message);
    }

    final public function assertEqualsBills(Bill|false $expectedBill, Bill|false $actualBill, int $fileCounter, string $message = ''): void
    {
        $expectedFile = "Expected json '$fileCounter.json'";
        $actualFile = "Actual bill '$fileCounter.txt'";
        // > Assert Bill Not False
        $this->assertNotFalse($expectedBill, "$expectedFile is false");
        $this->assertNotFalse($actualBill, "$actualFile is false");
        // < Assert Bill Not False

        // > Assert Bill Primitive Types
        $this->assertNotNull($expectedBill->installationCode, "$expectedFile - 'installationCode' is null");
        $this->assertNotNull($actualBill->installationCode, "$actualFile - 'installationCode' is null");
        $this->assertEquals($expectedBill->installationCode, $actualBill->installationCode, "$actualFile - 'installationCode' does not matches the $expectedFile");
        // < Assert Bill Primitive Types

        // > Assert Bill -> Client Primitive Types
        $this->assertNotNull($expectedBill->client, "$expectedFile - 'client' is null");
        $this->assertNotNull($actualBill->client, "$actualFile - 'client' is null");
        $this->assertEquals($expectedBill->client->name, $actualBill->client->name, "$actualFile - 'client' - 'name' does not matches the $expectedFile");
        // < Assert Bill -> Client Primitive Types

        // > Assert Bill -> Client -> Address Primitive Types
        $this->assertEquals($expectedBill->client->address->street, $actualBill->client->address->street, "$actualFile - 'client' - 'address' - 'street' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->client->address->district, $actualBill->client->address->district, "$actualFile - 'client' - 'address' - 'district' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->client->address->postcode, $actualBill->client->address->postcode, "$actualFile - 'client' - 'address' - 'postcode' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->client->address->city, $actualBill->client->address->city, "$actualFile - 'client' - 'address' - 'city' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->client->address->state, $actualBill->client->address->state, "$actualFile - 'client' - 'address' - 'state' does not matches the $expectedFile");
        // < Assert Bill -> Client -> Address Primitive Types

        // > Assert Bill -> Client -> Establishment Primitive Types
        $this->assertEquals($expectedBill->client->establishment->classification, $actualBill->client->establishment->classification, "$actualFile - 'client' - 'establishment' - 'classification' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->client->establishment->supplyType, $actualBill->client->establishment->supplyType, "$actualFile - 'client' - 'establishment' - 'supplyType' does not matches the $expectedFile");
        // < Assert Bill -> Client -> Establishment Primitive Types

        // > Assert Bill -> Dates Primitive Types
        $this->assertNotNull($expectedBill->dates, "$expectedFile - 'dates' is null");
        $this->assertNotNull($actualBill->dates, "$actualFile - 'dates' is null");
        $this->assertEqualsDate($expectedBill->dates->actualReadingDate, $actualBill->dates->actualReadingDate, "$actualFile - 'dates' - 'actualReadingDate' does not matches the $expectedFile");
        $this->assertEqualsDate($expectedBill->dates->nextReadingDate, $actualBill->dates->nextReadingDate, "$actualFile - 'dates' - 'nextReadingDate' does not matches the $expectedFile");
        $this->assertEqualsDate($expectedBill->dates->previousReadingDate, $actualBill->dates->previousReadingDate, "$actualFile - 'dates' - 'previousReadingDate' does not matches the $expectedFile");
        $this->assertEqualsDate($expectedBill->dates->date, $actualBill->dates->date, "$actualFile - 'dates' - 'date' does not matches the $expectedFile");
        // < Assert Bill -> Dates Primitive Types

        // > Assert Bill -> SolarGeneration Primitive Types
        $this->assertNotNull($expectedBill->solarGeneration, "$expectedFile - 'solarGeneration' is null");
        $this->assertNotNull($actualBill->solarGeneration, "$actualFile - 'solarGeneration' is null");
        $this->assertEquals($expectedBill->solarGeneration->balance, $actualBill->solarGeneration->balance, "$actualFile - 'solarGeneration' - 'balance' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->solarGeneration->toExpireNextMonth, $actualBill->solarGeneration->toExpireNextMonth, "$actualFile - 'solarGeneration' - 'toExpireNextMonth' does not matches the $expectedFile");
        // < Assert Bill -> SolarGeneration Primitive Types

        // > Assert Bill -> Debits
        $this->assertNotNull($expectedBill->debits, "$expectedFile - 'debits' is null");
        $this->assertNotNull($actualBill->debits, "$actualFile - 'debits' is null");
        $this->assertEquals($expectedBill->debits->tusd, $actualBill->debits->tusd, "$actualFile - 'debits' - 'tusd' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->debits->te, $actualBill->debits->te, "$actualFile - 'debits' - 'te' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->debits->cip, $actualBill->debits->cip, "$actualFile - 'debits' - 'cip' does not matches the $expectedFile");
        // < Assert Bill -> Debits

        // > Assert Bill -> Powers
        $this->assertNotNull($expectedBill->powers, "$expectedFile - 'powers' is null");
        $this->assertNotNull($actualBill->powers, "$actualFile - 'powers' is null");
        $this->assertEquals($expectedBill->powers->active, $actualBill->powers->active, "$actualFile - 'powers' - 'active' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->powers->injected, $actualBill->powers->injected, "$actualFile - 'powers' - 'injected' does not matches the $expectedFile");
        // < Assert Bill -> Powers

        // > Assert Bill -> Price (Money Object)
        $this->assertNotNull($expectedBill->price, "$expectedFile - 'price' is null");
        $this->assertNotNull($actualBill->price, "$actualFile - 'price' is null");
        $this->assertEquals($expectedBill->price, $actualBill->price, "$actualFile - 'price' does not matches the $expectedFile");
        // < Assert Bill -> Price (Money Object)

        // > Assert Bill -> Real Price (Money Object)
        $this->assertNotNull($expectedBill->realPrice, "$expectedFile - 'realPrice' is null");
        $this->assertNotNull($actualBill->realPrice, "$actualFile - 'realPrice' is null");
        $this->assertEquals($expectedBill->realPrice, $actualBill->realPrice, "$actualFile - 'realPrice' does not matches the $expectedFile");
        // < Assert Bill -> Real Price (Money Object)

        // > Assert Bill -> Last Month Price (Money Object)
        $this->assertNotNull($expectedBill->lastMonthPrice, "$expectedFile - 'lastMonthPrice' is null");
        $this->assertNotNull($actualBill->lastMonthPrice, "$actualFile - 'lastMonthPrice' is null");
        $this->assertEquals($expectedBill->lastMonthPrice, $actualBill->lastMonthPrice, "$actualFile - 'lastMonthPrice' does not matches the $expectedFile");
        // < Assert Bill -> Last Month Price (Money Object)
    }

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

            $expectedBill = $this->jsonToBill((object) json_decode($expectedFileContents, false));
            $actualBill = $extractor->extract();

            $this->assertEqualsBills($expectedBill, $actualBill, $i);
        }
    }

    /**
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedArgument
     * @psalm-suppress PossiblyFalseArgument
     * @psalm-suppress PossiblyNullArgument
     */
    public function jsonToBill(object $json): Bill|false
    {
        $dateFormatReset = '!d/m/Y';
        $monthFormatReset = '!m/y';
        $bill = new Bill(
            new Client(
                $json->client->name,
                new Address(
                    $json->client->address->street,
                    $json->client->address->district,
                    $json->client->address->postcode,
                    $json->client->address->city,
                    $json->client->address->state,
                ),
                new Establishment(
                    $json->client->establishment->classification,
                    $json->client->establishment->supplyType
                )
            ),
            new Dates(
                \DateTime::createFromFormat($dateFormatReset, $json->dates->actualReadingDate),
                \DateTime::createFromFormat($dateFormatReset, $json->dates->previousReadingDate),
                \DateTime::createFromFormat($dateFormatReset, $json->dates->nextReadingDate),
                \DateTime::createFromFormat($monthFormatReset, $json->dates->date)
            ),
            new SolarGeneration(
                $json->solarGeneration->balance,
                $json->solarGeneration->toExpireNextMonth,
            ),
            $json->installationCode,
            new Debits(
                $this->jsonToDebit($json->debits->tusd),
                $this->jsonToDebit($json->debits->te),
                $this->jsonToDebit($json->debits->cip),
            ),
            new Powers(
                $this->jsonToPower($json->powers->active),
                $this->jsonToPower($json->powers->injected)
            ),
            NumericHelper::numericStringToMoney($json->price),
            NumericHelper::numericStringToMoney($json->realPrice),
            NumericHelper::numericStringToMoney($json->lastMonthPrice),
        );

        return $bill;
    }

    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress PossiblyFalseArgument
     */
    private function jsonToDebit(?object $json): ?Debit
    {
        if (!$json) {
            return null;
        }

        return new Debit(
            NumericHelper::numericStringToMoney($json->price, negativeOnEnd: true),
            $json->name,
            $json->abbreviation,
            $json->kWhAmount ?? null
        );
    }

    /**
     * @psalm-suppress MixedArgument
     */
    private function jsonToPower(?object $json): ?Power
    {
        if (!$json) {
            return null;
        }

        return new Power(
            $json->actualReading,
            $json->previousReading,
            $json->kWhAmount,
        );
    }
}
