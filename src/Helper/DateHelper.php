<?php

namespace NystronSolar\ElectricBillExtractor\Helper;

use DateTime;

class DateHelper
{
    public static function getPortugueseMonths(bool $abbreviate = false): array
    {
        $fullList = [
            1 => "Janeiro",
            2 => "Fevereiro",
            3 => "Março",
            4 => "Abril",
            5 => "Maio",
            6 => "Junho",
            7 => "Julho",
            8 => "Agosto",
            9 => "Setembro",
            10 => "Outubro",
            11 => "Novembro",
            12 => "Dezembro",
        ];

        $abbreviatedList = [
            1 => "JAN",
            2 => "FEV",
            3 => "MAR",
            4 => "ABR",
            5 => "MAI",
            6 => "JUN",
            7 => "JUL",
            8 => "AGO",
            9 => "SET",
            10 => "OUT",
            11 => "NOV",
            12 => "DEZ",
        ];

        return $abbreviate ? $abbreviatedList : $fullList;
    }

    public static function fromMonthYearPortuguese(string $strDate, bool $abbreviate = false): bool|DateTime
    {
        return self::fromMonthYear(self::getPortugueseMonths($abbreviate), $strDate);
    }

    public static function fromMonthYear(array $list, string $strDate, string $separator = '/'): bool|DateTime
    {
        $strDateExplode = explode($separator, $strDate);
        $strMonth = $strDateExplode[0];
        $month = array_search($strMonth, $list);
        if (!$month) {
            return false;
        }

        $year = $strDateExplode[1];

        $date = DateTime::createFromFormat('d/m/Y', "01/$month/$year");

        return $date;
    }
}