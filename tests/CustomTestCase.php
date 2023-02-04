<?php

namespace App\Tests;

use ArrayAccess;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Parser;

abstract class CustomTestCase extends TestCase
{
    /**
     * Asserts that a PDF file can be loaded.
     * @param string $pdf The PDF Path
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public static function assertPDF(string $pdf): void
    {
        static::assertFileExists($pdf);

        $parser = new Parser();
        $parser->parseFile($pdf);
    }

    /**
     * Asserts that an array has an specified array of keys.
     *
     * @param array|ArrayAccess $key
     * @param array|ArrayAccess $array
     * @param string            $message
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    public static function assertArrayHasKeys(array |ArrayAccess $keys, array |ArrayAccess $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            static::assertArrayHasKey($key, $array, sprintf($message, $key));
        }
    }

    /**
     * Asserts that two dates have the same day, month and year.
     * 
     * @param DateTimeInterface $expected
     * @param DateTimeInterface $array
     * @param string            $message
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    public static function assertSameDate(DateTimeInterface $expected, DateTimeInterface $actual, string $message = ''): void
    {
        $expectedDate = date_format($expected, "m/d/Y");
        $actualDate = date_format($actual, "m/d/Y");

        static::assertSame($expectedDate, $actualDate, $message);
    }

    /**
     * Asserts that a method exists in a class.
     * 
     * @param string $class
     * @param string $method
     * @param string $message
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    public static function assertMethodExists(string $class, string $method, string $message = ''): void
    {
        static::assertTrue(method_exists($class, $method), $message);
    }
}