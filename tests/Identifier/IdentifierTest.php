<?php

namespace App\Tests\Identifier;

use NystronSolar\ElectricBillExtractor\Identifier\ExtractorIdentifier;
use NystronSolar\ElectricBillExtractor\Identifier\Identifier;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class IdentifierTest extends TestCase
{
    public function fakeContentProvider(): array
    {
        return [
            ['DANF3E', ExtractorIdentifier::RGE],
            ['Other', ExtractorIdentifier::OldRGE]
        ];
    }

    public function extractorIdentifierProvider(): array
    {
        return [
            [ExtractorIdentifier::RGE],
            [ExtractorIdentifier::OldRGE],
        ];
    }

    /** @dataProvider fakeContentProvider */
    public function testIdentifyFromContent(string $content, ExtractorIdentifier $expectedExtractorIdentifier)
    {
        $actualExtractorIdentifier = Identifier::identifyFromContent($content);

        $this->assertSame($expectedExtractorIdentifier, $actualExtractorIdentifier);
    }

    /** @dataProvider extractorIdentifierProvider */
    public function testInstantiateWithoutArguments(ExtractorIdentifier $extractorIdentifier)
    {
        $extractor = $extractorIdentifier->instantiate();

        $this->assertNotNull($extractor->getParser());
    }

    /** @dataProvider extractorIdentifierProvider */
    public function testInstantiateWithArguments(ExtractorIdentifier $extractorIdentifier)
    {
        $config = new Config();
        $config->setFontSpaceLimit($fontSpaceLimit = -60);
        $parser = new Parser(config: $config);

        $extractor = $extractorIdentifier->instantiate(['parser' => $parser]);

        $this->assertSame($fontSpaceLimit, $extractor->getParser()->getConfig()->getFontSpaceLimit());
    }
}