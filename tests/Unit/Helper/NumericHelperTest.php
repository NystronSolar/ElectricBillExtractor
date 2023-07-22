<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Helper;

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
}
