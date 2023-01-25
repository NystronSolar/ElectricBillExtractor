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
}