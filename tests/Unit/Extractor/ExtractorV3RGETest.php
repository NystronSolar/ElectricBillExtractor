<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Extractor;

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV1RGE;
use NystronSolar\ElectricBillExtractorTests\TestCase\ExtractorTestCase;

class ExtractorV3RGETest extends ExtractorTestCase
{
    public function testExtraction(): void
    {
        $this->assertByContentFolder('V3RGE', ExtractorV1RGE::class);
    }
}
