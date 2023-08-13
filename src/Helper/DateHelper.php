<?php

namespace NystronSolar\ElectricBillExtractor\Helper;

class DateHelper
{
    /**
     * Returns the number of the short month in PT/BR. (Case Insensitive)
     * Example: 'jan' => 1, 'mar' => 3.
     */
    public static function getShortMonthNumberPtBr(string $month): int|false
    {
        $months = [
            1 => 'jan',
            2 => 'fev',
            3 => 'mar',
            4 => 'abr',
            5 => 'mai',
            6 => 'jun',
            7 => 'jul',
            8 => 'ago',
            9 => 'set',
            10 => 'out',
            11 => 'nov',
            12 => 'dez',
        ];

        return static::foundMonthNumber($months, $month);
    }

    /**
     * Returns the number of the full month in PT/BR. (Case Insensitive)
     * Example: 'JANUARY' => 1, 'MARCH' => 3.
     */
    public static function getFullMonthNumberPtBr(string $month): int|false
    {
        $months = [
            1 => 'janeiro',
            2 => 'fevereiro',
            3 => 'março',
            4 => 'abril',
            5 => 'maio',
            6 => 'junho',
            7 => 'julho',
            8 => 'agosto',
            9 => 'setembro',
            10 => 'outubro',
            11 => 'novembro',
            12 => 'dezembro',
        ];

        return static::foundMonthNumber($months, $month);
    }

    /**
     * Returns the number of the month, following the months array. (Case Insensitive).
     *
     * @param non-empty-array<int, string> $months
     */
    public static function foundMonthNumber(array $months, string $month): int|false
    {
        $month = str_replace('Ç', 'C', $month);
        foreach ($months as $key => $currentMonth) {
            $currentMonthFix = str_replace('Ç', 'C', $currentMonth);
            $currentMonthFix = str_replace('ç', 'c', $currentMonthFix);

            if (strtolower(trim($currentMonthFix)) === strtolower(trim($month))) {
                return $key;
            }
        }

        return false;
    }
}
