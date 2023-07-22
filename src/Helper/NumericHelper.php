<?php

namespace NystronSolar\ElectricBillExtractor\Helper;

class NumericHelper
{
    /**
     * @return numeric-string|false
     */
    public static function brazilianNumberToNumericString(string $rawNumber): string|false
    {
        $result = str_replace('.', '', $rawNumber);
        $result = str_replace(',', '.', $result);

        if (!str_contains($result, '.')) {
            $result .= '.0';
        }

        if (str_ends_with($result, '.')) {
            $result .= '0';
        }

        if (str_starts_with($result, '.')) {
            $result = '0'.$result;
        }

        $result = preg_replace('/0{2,}$/', '0', $result);

        if (!is_numeric($result)) {
            return false;
        }

        return $result;
    }
}
