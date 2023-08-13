<?php

namespace NystronSolar\ElectricBillExtractor\Entity;

class Dates
{
    public function __construct(
        public readonly \DateTimeInterface $actualReadingDate,
        public readonly \DateTimeInterface $previousReadingDate,
        public readonly \DateTimeInterface $nextReadingDate,
    ) {
    }
}
