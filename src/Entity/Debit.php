<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

use TheDevick\PreciseMoney\Money;

class Debit
{
    /**
     * @param numeric-string|null $kWhAmount
     */
    public function __construct(
        public readonly Money $price,
        public readonly string $name,
        public readonly ?string $abbreviation,
        public readonly ?string $kWhAmount = null
    ) {
    }
}
