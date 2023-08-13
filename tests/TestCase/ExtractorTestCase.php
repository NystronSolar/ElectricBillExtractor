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
use TheDevick\PreciseMoney\Money;

class ExtractorTestCase extends TestCase
{
    final public function assertEqualsDate(?\DateTimeInterface $expected, ?\DateTimeInterface $actual, string $message = '', string $format = 'd/m/Y'): void
    {
        $this->assertNotNull($expected);
        $this->assertNotNull($actual);
        $this->assertEquals($expected->format($format), $actual->format($format), $message);
    }

    final public function assertEqualsMoney(Money $expected, Money $actual, string $message = ''): void
    {
        $this->assertSame(NumericHelper::cleanNumericString($expected->getAmount()), NumericHelper::cleanNumericString($actual->getAmount()), $message);
    }

    final public function assertEqualsDebit(?Debit $expected, ?Debit $actual, string $expectedFile = '', string $actualFile = '', string $debit = ''): void
    {
        if (is_null($expected) && is_null($actual)) {
            return;
        }

        $this->assertNotNull($expected, "$expectedFile - 'debits' - '$debit' - is null.");
        $this->assertNotNull($actual, "$actualFile - 'debits' - '$debit' - is null.");
        $this->assertSame($expected->abbreviation, $actual->abbreviation, "$actualFile - 'debits' - '$debit' - 'abbreviation' does not matches the $expectedFile");
        $this->assertSame($expected->kWhAmount, $actual->kWhAmount, "$actualFile - 'debits' - '$debit' - 'kWhAmount' does not matches the $expectedFile");
        $this->assertSame($expected->name, $actual->name, "$actualFile - 'debits' - '$debit' - 'name' does not matches the $expectedFile");
        $this->assertEqualsMoney($expected->price, $actual->price, "$actualFile - 'debits' - '$debit' - 'price' does not matches the $expectedFile");
    }

    final public function assertEqualsBills(Bill|false $expectedBill, Bill|false $actualBill, int $fileCounter, string $message = ''): void
    {
        $expectedFile = "Expected json '$fileCounter.json'";
        $actualFile = "Actual bill '$fileCounter.txt'";

        // > Assert Bill Not False
        $this->assertNotFalse($expectedBill, "$expectedFile is false");
        $this->assertNotFalse($actualBill, "$actualFile is false");
        // < Assert Bill Not False

        // > Assert Bill Values Not Null
        $this->assertNotNull($expectedBill->installationCode, "$expectedFile - 'installationCode' is null");
        $this->assertNotNull($actualBill->installationCode, "$actualFile - 'installationCode' is null");

        $this->assertNotNull($expectedBill->client, "$expectedFile - 'client' is null");
        $this->assertNotNull($actualBill->client, "$actualFile - 'client' is null");

        $this->assertNotNull($expectedBill->dates, "$expectedFile - 'dates' is null");
        $this->assertNotNull($actualBill->dates, "$actualFile - 'dates' is null");

        $this->assertNotNull($expectedBill->debits, "$expectedFile - 'debits' is null");
        $this->assertNotNull($actualBill->debits, "$actualFile - 'debits' is null");

        $this->assertNotNull($expectedBill->powers, "$expectedFile - 'powers' is null");
        $this->assertNotNull($actualBill->powers, "$actualFile - 'powers' is null");

        $this->assertNotNull($expectedBill->price, "$expectedFile - 'price' is null");
        $this->assertNotNull($actualBill->price, "$actualFile - 'price' is null");

        $this->assertNotNull($expectedBill->realPrice, "$expectedFile - 'realPrice' is null");
        $this->assertNotNull($actualBill->realPrice, "$actualFile - 'realPrice' is null");

        $this->assertNotNull($expectedBill->lastMonthPrice, "$expectedFile - 'lastMonthPrice' is null");
        $this->assertNotNull($actualBill->lastMonthPrice, "$actualFile - 'lastMonthPrice' is null");
        // < Assert Bill Values Not Null

        // > Assert Bill
        $this->assertEquals($expectedBill->installationCode, $actualBill->installationCode, "$actualFile - 'installationCode' does not matches the $expectedFile");
        $this->assertEqualsMoney($expectedBill->price, $actualBill->price, "$actualFile - 'price' does not matches the $expectedFile");
        $this->assertEqualsMoney($expectedBill->realPrice, $actualBill->realPrice, "$actualFile - 'realPrice' does not matches the $expectedFile");
        $this->assertEqualsMoney($expectedBill->lastMonthPrice, $actualBill->lastMonthPrice, "$actualFile - 'lastMonthPrice' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->client, $actualBill->client, "$actualFile - 'client' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->solarGeneration, $actualBill->solarGeneration, "$actualFile - 'solarGeneration' does not matches the $expectedFile");
        $this->assertEquals($expectedBill->powers, $actualBill->powers, "$actualFile - 'powers' does not matches the $expectedFile");
        $this->assertEqualsDate($expectedBill->date, $actualBill->date, "$actualFile - 'date' does not matches the $expectedFile");
        // < Assert Bill

        // > Assert Debits
        $this->assertEqualsDebit($expectedBill->debits->tusd, $actualBill->debits->tusd, $expectedFile, $actualFile, 'tusd');
        $this->assertEqualsDebit($expectedBill->debits->te, $actualBill->debits->te, $expectedFile, $actualFile, 'te');
        $this->assertEqualsDebit($expectedBill->debits->cip, $actualBill->debits->cip, $expectedFile, $actualFile, 'cip');
        $this->assertEqualsDebit($expectedBill->debits->discounts, $actualBill->debits->discounts, $expectedFile, $actualFile, 'discounts');
        $this->assertEqualsDebit($expectedBill->debits->increases, $actualBill->debits->increases, $expectedFile, $actualFile, 'increases');
        // < Assert Debits

        // > Assert Bill -> Dates
        $this->assertEqualsDate($expectedBill->dates->actualReadingDate, $actualBill->dates->actualReadingDate, "$actualFile - 'dates' - 'actualReadingDate' does not matches the $expectedFile");
        $this->assertEqualsDate($expectedBill->dates->nextReadingDate, $actualBill->dates->nextReadingDate, "$actualFile - 'dates' - 'nextReadingDate' does not matches the $expectedFile");
        $this->assertEqualsDate($expectedBill->dates->previousReadingDate, $actualBill->dates->previousReadingDate, "$actualFile - 'dates' - 'previousReadingDate' does not matches the $expectedFile");
        // < Assert Bill -> Dates
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

            $expectedBill = $this->jsonToBill((object) json_decode($expectedFileContents, false), $extractorClass, $i);
            $actualBill = $extractor->extract();

            $this->assertEqualsBills($expectedBill, $actualBill, $i);
        }
    }

    /**
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedArgument
     * @psalm-suppress PossiblyFalseArgument
     * @psalm-suppress PossiblyNullArgument
     *
     * @param class-string<Extractor> $extractorClass
     */
    public function jsonToBill(object $json, string $extractorClass, int $actualFile): Bill|false
    {
        $dateFormatReset = '!d/m/Y';
        $monthFormatReset = '!m/y';
        try {
            $solarGeneration = !isset($json->solarGeneration) ? null : new SolarGeneration(
                $json->solarGeneration->balance,
                $json->solarGeneration->toExpireNextMonth,
            );

            $powerInjectedExists = isset($json->powers->injected);
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
                ),
                \DateTime::createFromFormat($monthFormatReset, $json->dates->date),
                $solarGeneration,
                $json->installationCode,
                new Debits(
                    new Debit(
                        NumericHelper::numericStringToMoney(
                            $json->debits->tusd->price,
                        ),
                        $json->debits->tusd->name,
                        $json->debits->tusd->abbreviation,
                        $json->debits->tusd->kWhAmount ?? null
                    ),
                    new Debit(
                        NumericHelper::numericStringToMoney(
                            $json->debits->te->price,
                        ),
                        $json->debits->te->name,
                        $json->debits->te->abbreviation,
                        $json->debits->te->kWhAmount ?? null
                    ),
                    new Debit(
                        NumericHelper::numericStringToMoney(
                            $json->debits->cip->price,
                        ),
                        $json->debits->cip->name,
                        $json->debits->cip->abbreviation,
                        $json->debits->cip->kWhAmount ?? null
                    ),
                ),
                new Powers(
                    new Power(
                        $json->powers->active->actualReading,
                        $json->powers->active->previousReading,
                        $json->powers->active->kWhAmount,
                    ),
                    !$powerInjectedExists ? null : new Power(
                        $json->powers->injected->actualReading,
                        $json->powers->injected->previousReading,
                        $json->powers->injected->kWhAmount,
                    ),
                ),
                NumericHelper::numericStringToMoney($json->price),
                NumericHelper::numericStringToMoney($json->realPrice),
                NumericHelper::numericStringToMoney($json->lastMonthPrice),
            );
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Error ocurred when converting JSON to Bill in %s - %s', $extractorClass, $actualFile));
        }

        return $bill;
    }
}
