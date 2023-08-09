<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Debits
{
    /** @var array<Debit> */
    public readonly array $debits;

    /**
     * @param array<Debit> $others
     */
    public function __construct(
        public readonly Debit $tusd,
        public readonly Debit $te,
        public readonly Debit $cip,
        public readonly ?Debit $discounts = null,
        public readonly ?Debit $increases = null,
        array $others = [],
    ) {
        $debits = [$tusd, $te, $cip];
        if (!is_null($discounts)) {
            $debits[] = $discounts;
        }
        if (!is_null($increases)) {
            $debits[] = $increases;
        }
        $debits = [...$debits, ...$others];
        $this->debits = $debits;
    }
}
