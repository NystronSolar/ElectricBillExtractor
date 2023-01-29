<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

use NystronSolar\ElectricBillExtractor\Bill;
use NystronSolar\ElectricBillExtractor\Extractor;

/**
 * @method \NystronSolar\ElectricBillExtractor\BR\RS\BillRGE getBill()
 * @method \NystronSolar\ElectricBillExtractor\BR\RS\BillRGE fromFile(string $filename): Bill|false
 * @method \NystronSolar\ElectricBillExtractor\BR\RS\BillRGE fromContent(string $content): Bill|false
 * @property \NystronSolar\ElectricBillExtractor\BR\RS\BillRGE $bill
 */
class ExtractorRGE extends Extractor
{
    protected function extract(string $content): Bill|false
    {
        $this->setBill(new BillRGE($this->getDocument()));

        foreach ($this->contentExploded as $key => $value) {
            $this->extractClient($value, $key);
            $this->extractBatch($value, $key);
            $this->extractReadingGuide($value, $key);
            $this->extractPowerMeterId($value, $key);
            $this->extractPages($value, $key);
        }

        return $this->getBill();
    }

    private function extractClient(string $value, int $key, bool $setClient = true): ClientRGE|false
    {
        if (str_starts_with($value, "Inscrição Estadual")) {
            $row = $key + 1;
            $name = $this->contentExploded[$row];
            $address = $this->contentExploded[$row + 1];
            $district = $this->contentExploded[$row + 2];
            $city = $this->contentExploded[$row + 3];

            $client = new ClientRGE($name, $address, $district, $city);

            if ($setClient) {
                $this->bill->setClient($client);
            }

            return $client;
        }

        return false;
    }

    private function extractBatch(string $value, int $key, bool $setBatch = true): int|false
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key - 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $batch = (int) $valuesArray[0];

            if ($setBatch) {
                $this->bill->setBatch($batch);
            }

            return $batch;
        }

        return false;
    }

    private function extractReadingGuide(string $value, int $key, bool $setReadingGuide = true): string|false
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key - 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $readingGuide = $valuesArray[1];

            if ($setReadingGuide) {
                $this->bill->setReadingGuide($readingGuide);
            }

            return $readingGuide;
        }

        return false;
    }

    private function extractPowerMeterId(string $value, int $key, bool $setPowerMeterId = true): string|false
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key - 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $powerMeterId = (int) $valuesArray[2];

            if ($setPowerMeterId) {
                $this->bill->setPowerMeterId($powerMeterId);
            }

            return $powerMeterId;
        }

        return false;
    }

    private function extractPages(string $value, int $key, bool $setPages = true): array|false
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key - 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $pages = [
                'Actual' => (int) $valuesArray[3],
                'Total' => (int) $valuesArray[4]
            ];

            if ($setPages) {
                $this->bill->setPages($pages);
            }

            return $pages;
        }

        return false;
    }
}