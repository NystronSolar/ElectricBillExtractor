<?php

namespace NystronSolar\ElectricBillExtractor\Identifier;

use NystronSolar\ElectricBillExtractor\Extractor;
use Smalot\PdfParser\Parser;

class Identifier
{
    public static function identifyFromContent(string $content): ExtractorIdentifier
    {
        if (str_starts_with($content, "DANF3E")) {
            return ExtractorIdentifier::RGE;
        }

        return ExtractorIdentifier::OldRGE;
    }
    public static function identifyFromFile(string $path, Parser $parser = null): ExtractorIdentifier
    {
        $parser = $parser ?? new Parser();
        $document = $parser->parseFile($path);
        $content = $document->getText();

        return static::identifyFromContent($content);
    }

    public static function instantiateFromContent(string $content, array $arguments = []): Extractor
    {
        $extractorIdentifier = static::identifyFromContent($content);

        return $extractorIdentifier->instantiate($arguments);
    }

    public static function instantiateFromFile(string $path, array $arguments = [], Parser $parser = null): Extractor
    {
        $extractorIdentifier = static::identifyFromFile($path, $parser);

        return $extractorIdentifier->instantiate($arguments);
    }

    public static function extractFromContent(string $content, array $instantiateArguments = []): array|bool
    {
        $extractor = static::instantiateFromContent($content, $instantiateArguments);

        return $extractor->fromContent($content);
    }

    public static function extractFromFile(string $path, array $instantiateArguments = [], Parser $parser = null): array|bool
    {
        $parser = $parser ?? new Parser();
        $document = $parser->parseFile($path);
        $content = $document->getText();

        $extractor = static::instantiateFromContent($content, $instantiateArguments);

        return $extractor->fromDocument($document);
    }
}