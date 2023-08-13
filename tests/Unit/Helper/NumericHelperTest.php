<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Helper;

use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;
use PHPUnit\Framework\TestCase;
use TheDevick\PreciseMoney\Money;

class NumericHelperTest extends TestCase
{
    private function generateMessage(string $actual, string $expected, string $method): string
    {
        return sprintf('Failed asserting that raw number %s returns %s in NumericHelper::%s() method', $actual, $expected, $method);
    }

    public function testBrazilianNumberToNumericString(): void
    {
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString(''), $this->generateMessage('""', '"0.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString(',0'), $this->generateMessage('",0"', '"0.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString('0,0'), $this->generateMessage('"0,0"', '"0.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString('0,00'), $this->generateMessage('"0,00"', '"0.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('15.0', NumericHelper::brazilianNumberToNumericString('15'), $this->generateMessage('"15"', '"15.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('15.0', NumericHelper::brazilianNumberToNumericString('15,'), $this->generateMessage('"15"', '"15.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('15.0', NumericHelper::brazilianNumberToNumericString('15,0'), $this->generateMessage('"15,0"', '"15.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150000'), $this->generateMessage('"150000"', '"150000.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150000,'), $this->generateMessage('"150000"', '"150000.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150000,0'), $this->generateMessage('"150000,0"', '"150000.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150.000'), $this->generateMessage('"150.000"', '"150000.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150.000,'), $this->generateMessage('"150.000"', '"150000.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150.000,0'), $this->generateMessage('"150.000,0"', '"150000.0"', 'brazilianNumberToNumericString'));
        $this->assertSame('150.0', NumericHelper::brazilianNumberToNumericString('150,0000'), $this->generateMessage('"150,0000"', '"15.0"', 'brazilianNumberToNumericString'));
    }

    public function testCleanNumericString(): void
    {
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0'), $this->generateMessage('"0"', '"0.0"', 'cleanNumericString'));
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0.'), $this->generateMessage('"0".', '"0.0"', 'cleanNumericString'));
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0000.'), $this->generateMessage('"0000".', '"0.0"', 'cleanNumericString'));
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0000'), $this->generateMessage('"0000"', '"0.0"', 'cleanNumericString'));
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0.0'), $this->generateMessage('"0.0"', '"0.0"', 'cleanNumericString'));
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0.0000'), $this->generateMessage('"0.0000"', '"0.0"', 'cleanNumericString'));
        $this->assertEquals('0.0', NumericHelper::cleanNumericString('0000.000'), $this->generateMessage('"0000.000"', '"0.0"', 'cleanNumericString'));
    }

    public function testNumericStringToMoney(): void
    {
        $this->assertEquals(new Money('0'), NumericHelper::numericStringToMoney('0'), $this->generateMessage('"0"', 'Money(0)', 'numericStringToMoney'));
        $this->assertEquals(new Money('15'), NumericHelper::numericStringToMoney('15'), $this->generateMessage('"15"', 'Money(1500)', 'numericStringToMoney'));
        $this->assertEquals(new Money('150'), NumericHelper::numericStringToMoney('150'), $this->generateMessage('"150"', 'Money(15000)', 'numericStringToMoney'));
        $this->assertEquals(new Money('1500'), NumericHelper::numericStringToMoney('1500'), $this->generateMessage('"1500"', 'Money(150000)', 'numericStringToMoney'));
        $this->assertEquals(new Money('150000'), NumericHelper::numericStringToMoney('150000'), $this->generateMessage('"150000"', 'Money(15000000)', 'numericStringToMoney'));
        $this->assertEquals(new Money('1500000'), NumericHelper::numericStringToMoney('1500000'), $this->generateMessage('"1500000"', 'Money(150000000)', 'numericStringToMoney'));
        $this->assertEquals(new Money('19.99'), NumericHelper::numericStringToMoney('19.99'), $this->generateMessage('"19.99"', 'Money(19.99)', 'numericStringToMoney'));
        $this->assertEquals(new Money('19.123'), NumericHelper::numericStringToMoney('19.123'), $this->generateMessage('"19.123"', 'Money(19.12)', 'numericStringToMoney'));
        $this->assertEquals(new Money('19.125'), NumericHelper::numericStringToMoney('19.125'), $this->generateMessage('"19.125"', 'Money(19.12)', 'numericStringToMoney'));
        $this->assertEquals(new Money('19.129'), NumericHelper::numericStringToMoney('19.129'), $this->generateMessage('"19.129"', 'Money(19.13)', 'numericStringToMoney'));
    }
}
