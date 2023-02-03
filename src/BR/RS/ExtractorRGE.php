<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

use DateTimeImmutable;
use DateTimeInterface;
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
            $this->extractHeader($value, $key);
            $this->extractClassification($value);
            $this->extractSupplyType($value, $key);
            $this->extractVoltage($value);
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

    private function extractHeader(string $value, int $key, bool $setHeader = true): array |false
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key - 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $header = [
                "Batch" => (int) $valuesArray[0],
                "ReadingGuide" => $valuesArray[1],
                "PowerMeterId" => (int) $valuesArray[2],
                "Pages" => [
                    'Actual' => (int) $valuesArray[3],
                    'Total' => (int) $valuesArray[4]
                ],
                "DeliveryDate" => DateTimeImmutable::createFromFormat("d/m/Y", $valuesArray[5]),
                "NextReadingDate" => DateTimeImmutable::createFromFormat("d/m/Y", $valuesArray[6]),
                "DueDate" => DateTimeImmutable::createFromFormat("d/m/Y", $valuesArray[7])
            ];

            if ($setHeader) {
                $this->bill->setBatch($header["Batch"]);
                $this->bill->setReadingGuide($header["ReadingGuide"]);
                $this->bill->setPowerMeterId($header["PowerMeterId"]);
                $this->bill->setPages($header["Pages"]);
                $this->bill->setDeliveryDate($header["DeliveryDate"]);
                $this->bill->setNextReadingDate($header["NextReadingDate"]);
                $this->bill->setDueDate($header["DueDate"]);
            }

            return $header;
        }

        return false;
    }

    private function extractClassification(string $value, bool $setClassification = true): string|false
    {
        if (str_starts_with($value, " Classificação:")) {
            $classification = substr($value, 20, -22);

            if ($setClassification) {
                $this->bill->setClassification($classification);
            }

            return $classification;
        }

        return false;
    }

    private function extractSupplyType(string $value, int $key, bool $setSupplyType = true): string|false
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key + 1;
            $supplyType = $this->contentExploded[$row];

            if ($setSupplyType) {
                $this->bill->setSupplyType($supplyType);
            }

            return $supplyType;
        }

        return false;
    }

    private function extractVoltage(string $value, bool $setVoltage = true): int|false
    {
        if (str_starts_with($value, "TENSÃO NOMINAL EM VOLTS")) {
            $voltage = (int) substr($value, 33, -32);

            if ($setVoltage) {
                $this->bill->setVoltage($voltage);
            }

            return $voltage;
        }

        return false;
    }
}