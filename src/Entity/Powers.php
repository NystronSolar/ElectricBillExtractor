<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Powers
{
    /** @var array<Power> */
    public readonly array $powers;

    public function __construct(
        public readonly Power $active,
        public readonly ?Power $injected = null,
    ) {
        $powers = [$active];
        if (!is_null($injected)) {
            $powers[] = $injected;
        }

        $this->powers = $powers;
    }
}
