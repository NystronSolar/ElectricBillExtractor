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
            $bill = $this->extractLoop();
        } catch (\Exception) {
            return false;
        }

        if (!$bill) {
            return false;
        }

        return $bill->isValid() ? $bill : false;
    }

    abstract protected function extractLoop(): Bill|false;
}
