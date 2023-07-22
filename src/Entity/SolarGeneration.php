<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class SolarGeneration
{
    /**
     * @param numeric-string $balance
     * @param numeric-string $toExpireNextMonth
     */
    public function __construct(
        public readonly string $balance,
        public readonly string $toExpireNextMonth,
    ) {
    }
}
