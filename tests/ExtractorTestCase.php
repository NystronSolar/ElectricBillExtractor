<?php

namespace App\Tests;

use App\Tests\CustomTestCase;
use NystronSolar\ElectricBillExtractor\Extractor;

abstract class ExtractorTestCase extends CustomTestCase
{
    protected Extractor $extractor;

    protected abstract function generateExtractor(): Extractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = $this->getExtractor();
    }

    public function getExtractor(): Extractor
    {
        return $this->extractor ?? $this->generateExtractor();
    }

    public function generateProvider(string $country, string $state, string $prefix): array
    {
        $paths = sprintf('tests/content/%s/%s/%s*', $country, $state, $prefix);
        $pdfAvailableFiles = glob($paths . '.pdf');
        $jsonAvailableFiles = glob($paths . '.json');

        if (count($pdfAvailableFiles) !== count($jsonAvailableFiles)) {
            throw new \Exception(sprintf("JSON or PDF Files are missing in \"%s\"", $paths));
        }

        $data = [];

        foreach ($pdfAvailableFiles as $key => $pdfPath) {
            $jsonPath = $jsonAvailableFiles[$key];

            $data[] = [
                'expected' => $this->readJson($jsonPath),
                'actual' => $this->readPDF($pdfPath),
            ];
        }

        return $data;
    }

    public static function readJson(string $path, bool $datesToObject = true): array|null
    {
        $jsonFile = $path;
        $jsonContent = file_get_contents($jsonFile);
        $json = json_decode($jsonContent, true);

        if ($datesToObject) {
            foreach ($json as $key => $value) {
                if (is_string($value) && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value)) {
                    $json[$key] = \DateTime::createFromFormat('m/d/Y', $value);
                }
            }
        }

        return $json;
    }

    public function readPDF(string $path): array
    {
        $billFile = $path;
        $bill = $this->getExtractor()->fromFile($billFile);

        return $bill;
    }
}