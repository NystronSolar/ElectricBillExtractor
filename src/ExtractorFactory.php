<?php

namespace NystronSolar\ElectricBillExtractor;

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV1RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV2RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV3RGE;
use Smalot\PdfParser\Parser;

class ExtractorFactory
{
    /**
     * @return class-string<Extractor>
     */
    public static function identifyExtractorClassFromContent(string $content): string
    {
        // The V3 RGE Bill starts with "DANF3E - DOCUMENTO AUXILIAR DA  NOTA"
        if (str_starts_with($content, 'DANF3E - DOCUMENTO AUXILIAR DA  NOTA')) {
            return ExtractorV3RGE::class;
        }

        // The V2 RGE Bill contains in the 4th line "Ato Declaratório"
        $contentArr = explode(PHP_EOL, $content);
        if (str_contains($contentArr[3], 'Ato Declaratório')) {
            return ExtractorV2RGE::class;
        }

        // Otherwise, return the V1 RGE Bill
        return ExtractorV1RGE::class;
    }

    /**
     * @return class-string<Extractor>
     */
    public static function identifyExtractorClassFromFile(string $filename, Parser $parser = new Parser()): string
    {
        $document = $parser->parseFile($filename);
        $content = $document->getText();

        return static::identifyExtractorClassFromContent($content);
    }

    public static function instantiateExtractorFromContent(string $content): Extractor
    {
        $extractorClass = static::identifyExtractorClassFromContent($content);

        return new $extractorClass();
    }

    public static function instantiateExtractorFromFile(string $filename, Parser $parser = new Parser()): Extractor
    {
        $extractorClass = static::identifyExtractorClassFromFile($filename, $parser);

        return new $extractorClass();
    }
}
