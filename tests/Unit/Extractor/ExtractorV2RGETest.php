<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Extractor;

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV2RGE;
use NystronSolar\ElectricBillExtractorTests\TestCase\ExtractorTestCase;

class ExtractorV2RGETest extends ExtractorTestCase
{
    public function testExtraction(): void
    {
        $this->assertByContentFolder('V2RGE', ExtractorV2RGE::class);
    }
}
