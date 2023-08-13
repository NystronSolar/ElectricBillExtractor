<?php

namespace NystronSolar\ElectricBillExtractor\Helper;

use TheDevick\PreciseMoney\Money;

class NumericHelper
{
    /**
     * @return numeric-string|false
     */
    public static function brazilianNumberToNumericString(string $rawNumber, bool $negativeOnEnd = false): string|false
    {
        $result = str_replace('.', '', $rawNumber);
        $result = str_replace(',', '.', $result);

        if ($negativeOnEnd && str_ends_with($result, '-')) {
            $result = str_replace('-', '', $result);
            $result = '-'.$result;
        }

        if (!str_contains($result, '.')) {
            $result .= '.0';
        }

        if (str_ends_with($result, '.')) {
            $result .= '0';
        }

        if (str_starts_with($result, '.')) {
            $result = '0'.$result;
        }

        if (str_starts_with($result, '-.')) {
            $result = str_replace('-.', '-0.', $result);
        }

        $result = preg_replace('/0{2,}$/', '0', $result);

        if (!is_numeric($result)) {
            return false;
        }

        return $result;
    }

    /**
     * @param numeric-string $numericString
     *
     * @return false|numeric-string
     */
    public static function cleanNumericString(string $numericString): false|string
    {
        if (str_ends_with($numericString, '.')) {
            $numericString .= '0';
        }

        if (!str_contains($numericString, '.')) {
            $numericString .= '.0';
        } else {
            $numericString = rtrim($numericString, '0').'0';
        }

        $numericString = '0'.ltrim($numericString, '0');

        if (!is_numeric($numericString)) {
            return false;
        }

        return $numericString;
    }

    /**
     * @param numeric-string $amount
     */
    public static function numericStringToMoney(string $amount, bool $negativeOnEnd = false): Money|false
    {
        if ($negativeOnEnd && str_ends_with($amount, '-')) {
            $amount = str_replace('-', '', $amount);
            $amount = '-'.$amount;
        }

        if (!is_numeric($amount)) {
            return false;
        }

        return new Money($amount);
    }
}
