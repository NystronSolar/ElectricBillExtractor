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

    public function extract(): Bill|false
    {
        try {
            return $this->extractLoop();
        } catch (\Exception) {
            return false;
        }
    }

    abstract protected function extractLoop(): Bill|false;
}
