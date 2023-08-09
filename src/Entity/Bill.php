<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

use Money\Currency;
use Money\Money;

class Bill
{
    public function __construct(
        public ?Client $client = null,
        public ?Dates $dates = null,
        public ?SolarGeneration $solarGeneration = null,
        public ?string $installationCode = null,
        public ?Debits $debits = null,
        public ?Powers $powers = null,
        public ?Money $price = null,
        public ?Money $realPrice = null,
        public ?Money $lastMonthPrice = new Money(0, new Currency('BRL')),
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
            && !is_null($this->debits)
            && !is_null($this->powers)
            && !is_null($this->price)
            && !is_null($this->realPrice)
            && !is_null($this->lastMonthPrice)
        ;
    }
}
