<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Power
{
    /**
     * @param numeric-string $actualReading
     * @param numeric-string $previousReading
     * @param numeric-string $kWhAmount
     */
    public function __construct(
        public readonly string $actualReading,
        public readonly string $previousReading,
        public readonly string $kWhAmount
    ) {
    }
}
