<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

use Money\Money;

class Bill
{
    public function __construct(
        public ?Client $client = null,
        public ?Dates $dates = null,
        public ?SolarGeneration $solarGeneration = null,
        public ?string $installationCode = null,
        public ?Money $price = null,
        public ?Debits $debits = null,
        public ?Powers $powers = null,
    ) {
    }

    /**
     * Check if any properties is nullable.
     */
    public function isValid(): bool
    {
        return
            !is_null($this->client)
            && !is_null($this->dates)
            && !is_null($this->installationCode)
            && !is_null($this->price)
            && !is_null($this->debits)
            && !is_null($this->powers)
        ;
    }
}
