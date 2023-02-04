<?php

namespace App\Tests\Helper;

use App\Tests\CustomTestCase;
use DateTime;
use NystronSolar\ElectricBillExtractor\Helper\DateHelper;

class DateHelperTest extends CustomTestCase
{
    /** @test */
    public function test_from_month_year_portuguese_function()
    {
        $this->assertMonthYearLanguage("Portuguese");
    }

    private function assertMonthYearLanguage(string $language)
    {
        $language = ucfirst($language);
        $getMonthsMethod = "get" . $language . "Months";
        $fromMonthYearMethod = "fromMonthYear" . $language;
        static::assertMethodExists(DateHelper::class, $fromMonthYearMethod);
        static::assertMethodExists(DateHelper::class, $getMonthsMethod);

        $fullList = DateHelper::$getMonthsMethod();
        $abbreviatedList = DateHelper::$getMonthsMethod(true);

        for ($month = 1; $month <= 12; $month++) {
            $fullMonth = $fullList[$month];
            $abbreviatedMonth = $abbreviatedList[$month];

            static::assertSameDate(
                DateTime::createFromFormat('d/m/Y', "01/$month/2010"),
                DateHelper::$fromMonthYearMethod("$fullMonth/2010")
            );

            static::assertSameDate(
                DateTime::createFromFormat('d/m/Y', "01/$month/2020"),
                DateHelper::$fromMonthYearMethod("$fullMonth/2020")
            );

            static::assertSameDate(
                DateTime::createFromFormat('d/m/Y', "01/$month/2010"),
                DateHelper::$fromMonthYearMethod("$abbreviatedMonth/2010", true)
            );

            static::assertSameDate(
                DateTime::createFromFormat('d/m/Y', "01/$month/2020"),
                DateHelper::$fromMonthYearMethod("$abbreviatedMonth/2020", true)
            );

        }
    }
}