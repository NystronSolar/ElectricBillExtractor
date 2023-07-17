<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Client
{
    public function __construct(
        public readonly string $name,
        public readonly Address $address
    ) {
    }
}
