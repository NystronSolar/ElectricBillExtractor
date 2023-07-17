<?php

namespace NystronSolar\ElectricBillExtractor;

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV1RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV2RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV3RGE;
use Smalot\PdfParser\Parser;

class ExtractorFactory
{
    /**
     * @return false|class-string<Extractor>
     */
    public static function identifyExtractorClassFromContent(string $content): string|false
    {
        // The V3 RGE Bill starts with "DANF3E - DOCUMENTO AUXILIAR DA  NOTA"
        if (str_starts_with($content, 'DANF3E - DOCUMENTO AUXILIAR DA  NOTA')) {
            return ExtractorV3RGE::class;
        }

        // The V2 RGE Bill contains in the 4th line "Ato Declaratório"
        $contentArr = explode(PHP_EOL, $content);
        if (array_key_exists(3, $contentArr) && str_contains($contentArr[3], 'Ato Declaratório')) {
            return ExtractorV2RGE::class;
        }

        // The V1 RGE Bill have the exact text 4th line "Conta de Energia Elétrica\n"
        if (array_key_exists(4, $contentArr) && 'Conta de Energia Elétrica' === $contentArr[4]) {
            return ExtractorV1RGE::class;
        }

        return false;
    }

    /**
     * @return false|class-string<Extractor>
     */
    public static function identifyExtractorClassFromFile(string $filename, Parser $parser = new Parser()): string|false
    {
        $content = static::getParsedContentFromFile($filename, $parser);

        return static::identifyExtractorClassFromContent($content);
    }

    public static function instantiateExtractorFromContent(string $content): Extractor|false
    {
        $extractorClass = static::identifyExtractorClassFromContent($content);

        if (!$extractorClass) {
            return false;
        }

        return new $extractorClass($content);
    }

    public static function instantiateExtractorFromFile(string $filename, Parser $parser = new Parser()): Extractor|false
    {
        $content = static::getParsedContentFromFile($filename, $parser);

        if (!$content) {
            return false;
        }

        $extractorClass = static::identifyExtractorClassFromContent($content);

        if (!$extractorClass) {
            return false;
        }

        return new $extractorClass($content);
    }

    public static function extractFromContent(string $content): Bill|false
    {
        $extractorObject = static::instantiateExtractorFromContent($content);

        if (!$extractorObject) {
            return false;
        }

        return $extractorObject->extract();
    }

    public static function extractFromFile(string $filename, Parser $parser = new Parser()): Bill|false
    {
        $extractorObject = static::instantiateExtractorFromFile($filename, $parser);

        if (!$extractorObject) {
            return false;
        }

        return $extractorObject->extract();
    }

    public static function getParsedContentFromFile(string $filename, Parser $parser = new Parser()): string
    {
        $document = $parser->parseFile($filename);
        $content = $document->getText();

        return $content;
    }
}
