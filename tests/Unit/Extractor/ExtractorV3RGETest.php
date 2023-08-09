<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Extractor;

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV3RGE;
use NystronSolar\ElectricBillExtractorTests\TestCase\ExtractorTestCase;

class ExtractorV3RGETest extends ExtractorTestCase
{
    public function testExtraction(): void
    {
        $this->assertByContentFolder('V3RGE', ExtractorV3RGE::class);
    }
}
