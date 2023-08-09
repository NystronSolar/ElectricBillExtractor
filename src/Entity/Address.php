<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $district,
        public readonly string $postcode,
        public readonly string $city,
        public readonly string $state,
    ) {
    }
}
