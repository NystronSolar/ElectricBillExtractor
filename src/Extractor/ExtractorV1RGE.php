<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Extractor;

final class ExtractorV1RGE extends Extractor
{
    protected function extractLoop(): Bill|false
    {
        return false;
    }
}
