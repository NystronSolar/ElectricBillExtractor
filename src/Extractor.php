<?php

namespace NystronSolar\ElectricBillExtractor;

use NystronSolar\ElectricBillExtractor\Entity\Bill;

/**
 * @psalm-consistent-constructor
 */
abstract class Extractor
{
    public function __construct(public readonly string $content)
    {
    }

    abstract public function extract(): Bill|false;
}
