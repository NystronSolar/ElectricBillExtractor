<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

use DateTimeImmutable;
use NystronSolar\ElectricBillExtractor\Extractor;

class ExtractorRGE extends Extractor
{
    protected function extract(string $content): array
    {
        foreach ($this->contentExploded as $key => $value) {
            $this->extractClient($value, $key);
            $this->extractHeader($value, $key);
            $this->extractBuilding($value, $key);
        }

        return $this->getBill();
    }

    private function extractClient(string $value, int $key): bool
    {
        if (str_starts_with($value, "Inscrição Estadual")) {
            $row = $key + 1;

            $client = [
                "Name" => $this->contentExploded[$row],
                "Address" => $this->contentExploded[$row + 1],
                "District" => $this->contentExploded[$row + 2],
                "City" => $this->contentExploded[$row + 3],
            ];

            $this->bill['Client'] = $client;

            return true;
        }

        return false;
    }

    private function extractHeader(string $value, int $key): bool
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key - 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $this->bill["Batch"] = (int) $valuesArray[0];
            $this->bill["ReadingGuide"] = $valuesArray[1];
            $this->bill["PowerMeterId"] = (int) $valuesArray[2];
            $this->bill["Pages"] = [
                'Actual' => (int) $valuesArray[3],
                'Total' => (int) $valuesArray[4]
            ];
            $this->bill["DeliveryDate"] = DateTimeImmutable::createFromFormat("d/m/Y", $valuesArray[5]);
            $this->bill["NextReadingDate"] = DateTimeImmutable::createFromFormat("d/m/Y", $valuesArray[6]);
            $this->bill["DueDate"] = DateTimeImmutable::createFromFormat("d/m/Y", $valuesArray[7]);

            return true;
        }

        return false;
    }

    private function extractBuilding(string $value, int $key): bool
    {
        if (str_starts_with($value, " Classificação:")) {
            $supplyTypeRow = $key + 1;
            $voltageRow = $key + 2;

            $building = [
                "Classification" => substr($value, 20, -22),
                "SupplyType" => $this->contentExploded[$supplyTypeRow],
                "Voltage" => [
                    "Available" => (int) substr($this->contentExploded[$voltageRow], 33, -32),
                    "MinimumLimit" => (int) substr($this->contentExploded[$voltageRow], 49, -16),
                    "MaximumLimit" => (int) substr($this->contentExploded[$voltageRow], 68)
                ]
            ];
            $this->bill["Client"]["Building"] = $building;

            return true;
        }

        return false;
    }
}