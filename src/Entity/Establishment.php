<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Establishment
{
    public function __construct(
        public readonly string $classification,
        public readonly string $supplyType
    ) {
    }
}
