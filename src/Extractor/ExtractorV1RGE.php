<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

use NystronSolar\ElectricBillExtractor\Bill;
use NystronSolar\ElectricBillExtractor\Extractor;

final class ExtractorV1RGE extends Extractor
{
    public function extract(): Bill|false
    {
        return false;
    }
}
