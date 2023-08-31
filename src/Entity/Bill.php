<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

use TheDevick\PreciseMoney\Money;

class Bill
{
    public function __construct(
        public ?Client $client = null,
        public ?Dates $dates = null,
        public ?\DateTimeInterface $date = null,
        public ?SolarGeneration $solarGeneration = null,
        public ?string $installationCode = null,
        public ?Debits $debits = null,
        public ?Powers $powers = null,
        public ?Money $price = null,
    ) {
    }

    /**
     * Check if any properties is nullable.
     * 
     * @psalm-assert-if-true Client $this->client
     * @psalm-assert-if-true Dates $this->dates
     * @psalm-assert-if-true \DateTimeInterface $this->date
     * @psalm-assert-if-true string $this->installationCode
     * @psalm-assert-if-true Debits $this->debits
     * @psalm-assert-if-true Powers $this->powers
     * @psalm-assert-if-true Price $this->price
     */
    public function isValid(): bool
    {
        return
            !is_null($this->client)
            && !is_null($this->dates)
            && !is_null($this->date)
            && !is_null($this->installationCode)
            && !is_null($this->debits)
            && !is_null($this->powers)
            && !is_null($this->price)
        ;
    }
}
