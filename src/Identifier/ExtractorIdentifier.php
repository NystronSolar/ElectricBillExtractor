<?php

namespace NystronSolar\ElectricBillExtractor\Identifier;
use NystronSolar\ElectricBillExtractor\Extractor;

enum ExtractorIdentifier: string
{
    case RGE = 'NystronSolar\ElectricBillExtractor\BR\RS\ExtractorRGE';
    case OldRGE = 'NystronSolar\ElectricBillExtractor\BR\RS\ExtractorOldRGE';

    public function instantiate(array $arguments = [])
    {
        if (!array_key_exists('parser', $arguments)) {
            array_unshift($arguments, null);
        }

        $arguments = array_values($arguments);

        /** @var Extractor $extractorClass */
        $extractorClass = $this->value;
        $extractor = new $extractorClass(...$arguments);

        return $extractor;
    }
}