<?php

namespace NystronSolar\ElectricBillExtractor\Helper;

use Money\Currency;
use Money\Money;

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

    /**
     * @param numeric-string $amount
     */
    public static function numericStringToMoney(string $amount, Currency $currency = new Currency('BRL')): Money|false
    {
        $amount = sprintf('%0.2f', $amount);

        $amount = str_replace('.', '', $amount);

        if (!is_numeric($amount)) {
            return false;
        }

        try {
            return new Money($amount, $currency);
        } catch (\InvalidArgumentException $e) {
            if ('Leading zeros are not allowed' === $e->getMessage()) {
                return new Money('0', $currency);
            }

            return false;
        }
    }
}
