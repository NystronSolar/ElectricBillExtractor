<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Bill
{
    public function __construct(
        public readonly Client $client
    ) {
    }
}