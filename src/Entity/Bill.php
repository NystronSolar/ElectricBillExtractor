<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Bill
{
    public function __construct(
        public ?Client $client = null,
        public ?Dates $dates = null
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
        ;
    }
}
