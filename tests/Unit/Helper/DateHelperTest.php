<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Helper;

use NystronSolar\ElectricBillExtractor\Helper\DateHelper;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    public function testGetShortMonthNumberPtBr(): void
    {
        $this->assertSame(1, DateHelper::getShortMonthNumberPtBr('Jan'));
        $this->assertSame(2, DateHelper::getShortMonthNumberPtBr('FEv'));
        $this->assertSame(3, DateHelper::getShortMonthNumberPtBr('MAR'));
        $this->assertSame(4, DateHelper::getShortMonthNumberPtBr('aBR'));
        $this->assertSame(5, DateHelper::getShortMonthNumberPtBr('maI'));
        $this->assertSame(6, DateHelper::getShortMonthNumberPtBr('jun'));
        $this->assertSame(7, DateHelper::getShortMonthNumberPtBr('jUl'));
        $this->assertFalse(DateHelper::getShortMonthNumberPtBr(' abc'));
        $this->assertFalse(DateHelper::getShortMonthNumberPtBr(' '));
        $this->assertFalse(DateHelper::getShortMonthNumberPtBr(''));
    }

    public function testGetFullMonthNumberPtBr(): void
    {
        $this->assertSame(1, DateHelper::getFullMonthNumberPtBr('Janeiro'));
        $this->assertSame(2, DateHelper::getFullMonthNumberPtBr('FEvereiro'));
        $this->assertSame(3, DateHelper::getFullMonthNumberPtBr('MARÇO'));
        $this->assertSame(4, DateHelper::getFullMonthNumberPtBr('aBRil'));
        $this->assertSame(5, DateHelper::getFullMonthNumberPtBr('maIO'));
        $this->assertSame(6, DateHelper::getFullMonthNumberPtBr('junho'));
        $this->assertSame(7, DateHelper::getFullMonthNumberPtBr('jUlho'));
        $this->assertFalse(DateHelper::getFullMonthNumberPtBr(' abc'));
        $this->assertFalse(DateHelper::getFullMonthNumberPtBr(' '));
        $this->assertFalse(DateHelper::getFullMonthNumberPtBr(''));
    }
}
