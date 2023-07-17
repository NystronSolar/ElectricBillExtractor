<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Extractor;

final class ExtractorV2RGE extends Extractor
{
    public function extract(): Bill|false
    {
        return false;
    }
}
