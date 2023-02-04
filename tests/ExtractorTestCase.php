<?php

namespace App\Tests;

use ArrayAccess;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Parser;

abstract class ExtractorTestCase extends CustomTestCase
{
    abstract protected static function getPaths(): array;

    abstract public static function assertBillJSON(array |string $json): void;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::assertBillJSON(static::getPaths()['json']);
    }
}