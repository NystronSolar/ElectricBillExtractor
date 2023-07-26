<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Helper;

use Money\Money;
use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;
use PHPUnit\Framework\TestCase;

class NumericHelperTest extends TestCase
{
    public function testBrazilianNumberToNumericString(): void
    {
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString(''), 'Failed asserting that raw number "" returns "0.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString(',0'), 'Failed asserting that raw number ",0" returns "0.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString('0,0'), 'Failed asserting that raw number "0,0" returns "0.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('0.0', NumericHelper::brazilianNumberToNumericString('0,00'), 'Failed asserting that raw number "0,00" returns "0.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('15.0', NumericHelper::brazilianNumberToNumericString('15'), 'Failed asserting that raw number "15" returns "15.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('15.0', NumericHelper::brazilianNumberToNumericString('15,'), 'Failed asserting that raw number "15" returns "15.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('15.0', NumericHelper::brazilianNumberToNumericString('15,0'), 'Failed asserting that raw number "15,0" returns "15.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150000'), 'Failed asserting that raw number "150000" returns "150000.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150000,'), 'Failed asserting that raw number "150000" returns "150000.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150000,0'), 'Failed asserting that raw number "150000,0" returns "150000.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150.000'), 'Failed asserting that raw number "150.000" returns "150000.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150.000,'), 'Failed asserting that raw number "150.000" returns "150000.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150000.0', NumericHelper::brazilianNumberToNumericString('150.000,0'), 'Failed asserting that raw number "150.000,0" returns "150000.0" in NumericHelper::brazilianNumberToNumericString() method');
        $this->assertSame('150.0', NumericHelper::brazilianNumberToNumericString('150,0000'), 'Failed asserting that raw number "150,0000" returns "15.0" in NumericHelper::brazilianNumberToNumericString() method');
    }

    public function testNumericStringToMoney(): void
    {
        $this->assertEquals(Money::BRL('0'), NumericHelper::numericStringToMoney('0'), 'Failed asserting that raw number "0" returns BRL(0) in NumericHelper::numericStringToMoney() method');
        $this->assertEquals(Money::BRL('15'), NumericHelper::numericStringToMoney('15'), 'Failed asserting that raw number "15" returns BRL(15) in NumericHelper::numericStringToMoney() method');
        $this->assertEquals(Money::BRL('150'), NumericHelper::numericStringToMoney('150'), 'Failed asserting that raw number "150" returns BRL(150) in NumericHelper::numericStringToMoney() method');
        $this->assertEquals(Money::BRL('1500'), NumericHelper::numericStringToMoney('1500'), 'Failed asserting that raw number "1500" returns BRL(1500) in NumericHelper::numericStringToMoney() method');
        $this->assertEquals(Money::BRL('150000'), NumericHelper::numericStringToMoney('150000'), 'Failed asserting that raw number "150000" returns BRL(150000) in NumericHelper::numericStringToMoney() method');
        $this->assertEquals(Money::BRL('1500000'), NumericHelper::numericStringToMoney('1500000'), 'Failed asserting that raw number "1500000" returns BRL(1500000) in NumericHelper::numericStringToMoney() method');
    }
}
