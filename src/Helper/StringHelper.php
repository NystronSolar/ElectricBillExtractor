<?php

namespace NystronSolar\ElectricBillExtractor\Helper;

class StringHelper
{
    public static function removeRepeatedWhitespace(string $value): string
    {
        $trim = trim($value);

        return (string) preg_replace('/\s+/', ' ', $trim);
    }
}
