<?php

namespace NystronSolar\ElectricBillExtractor\Identifier;

use NystronSolar\ElectricBillExtractor\Extractor;

class Identifier
{
    public static function identifyFromContent(string $content): ExtractorIdentifier
    {
        if (str_starts_with($content, "DANF3E")) {
            return ExtractorIdentifier::RGE;
        }

        return ExtractorIdentifier::OldRGE;
    }
    public static function identifyFromFile(string $path): ExtractorIdentifier
    {
        $content = file_get_contents($path);

        return static::identifyFromContent($content);
    }

    public static function instantiateFromContent(string $content, array $arguments = []): Extractor
    {
        $extractorIdentifier = static::identifyFromContent($content);

        return $extractorIdentifier->instantiate($arguments);
    }

    public static function instantiateFromFile(string $path, array $arguments = []): Extractor
    {
        $extractorIdentifier = static::identifyFromFile($path);

        return $extractorIdentifier->instantiate($arguments);
    }

    public static function extractFromContent(string $content, array $instantiateArguments = []): array|bool
    {
        $extractor = static::instantiateFromContent($content, $instantiateArguments);

        return $extractor->fromContent($content);
    }

    public static function extractFromFile(string $path, array $instantiateArguments = []): array|bool
    {
        $extractor = static::instantiateFromFile($path, $instantiateArguments);

        return $extractor->fromFile($path);
    }
}