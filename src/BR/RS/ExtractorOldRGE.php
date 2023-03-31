<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

use Money\Money;
use NystronSolar\ElectricBillExtractor\Extractor;
use NystronSolar\ElectricBillExtractor\Helper\DateHelper;

class ExtractorOldRGE extends Extractor
{
    protected function extract(): array
    {
        foreach ($this->contentExploded as $key => $value) {
            $this->extractClient($value, $key);
            $this->extractHeader($value, $key);
            $this->extractBuilding($value);
            $this->extractReadingDates($value, $key);
            $this->extractInstallationCode($value, $key);
            $this->extractBilling($value, $key);
            $this->extractNotices($value, $key);
            $this->extractSolarGeneration($value, $key);
            $this->extractEnergyData($value, $key);
        }

        return $this->getBill();
    }

    private function extractClient(string $value, int $key): bool
    {
        if (str_starts_with($value, "Data de Emissão")) {
            $row = $key - 2;
            $city = substr($this->contentExploded[$row - 1], 0, strpos($this->contentExploded[$row - 1], " Nota Fiscal"));

            $client = [
                "Name" => $this->contentExploded[$row - 4],
                "Address" => $this->contentExploded[$row - 3],
                "District" => $this->contentExploded[$row - 2],
                "City" => $city,
            ];

            $this->bill['Client'] = $client;

            return true;
        }

        return false;
    }

    private function extractHeader(string $value, int $key): bool
    {
        if (str_starts_with($value, "Lote Roteiro de leitura")) {
            $row = $key + 1;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $this->bill["Batch"] = (int) $valuesArray[0];
            $this->bill["ReadingGuide"] = $valuesArray[1];
            $this->bill["PowerMeterId"] = (int) $valuesArray[2];
            $this->bill["PN"] = (int) $valuesArray[3];

            return true;
        }

        return false;
    }

    private function extractBuilding(string $value): bool
    {
        if (str_starts_with($value, "CLASSIFICAÇÃO: ")) {

            $building = [
                "Classification" => str_replace('  ', ' ', substr($value, 17, -25)),
                "SupplyType" => explode(' ', substr($value, 48))[0],
                "Voltage" => (int) explode(' ', substr($value, -12, ))[0]
            ];

            $this->bill["Client"]["Building"] = $building;

            return true;
        }

        return false;
    }

    private function extractReadingDates(string $value, int $key): bool
    {
        if (str_starts_with($value, "Data de Emissão:")) {
            $actualReadingDate = \DateTime::createFromFormat('d/m/Y', substr($value, -10));
            $deliveryDate = \DateTime::createFromFormat('d/m/Y', substr($this->contentExploded[$key + 1], -10));
            $nextReadingDate = \DateTime::createFromFormat('d/m/Y', substr($this->contentExploded[$key + 4], -10));

            $this->bill["ActualReadingDate"] = $actualReadingDate;
            $this->bill["DeliveryDate"] = $deliveryDate;
            $this->bill["NextReadingDate"] = $nextReadingDate;
            $this->bill["Pages"] = [
                'Actual' => (int) substr(substr($this->contentExploded[$key + 2], 6), 0, strpos(substr($this->contentExploded[$key + 2], 6), " de")),
                'Total' => (int) str_replace(' ', '', substr(substr($this->contentExploded[$key + 2], 6), strpos(substr($this->contentExploded[$key + 2], 6), " de") + 4))
            ];

            return true;
        }

        return false;
    }

    private function extractInstallationCode(string $value, int $key): bool
    {
        if (str_starts_with($value, "www.rge-rs.com.br")) {
            $this->bill["InstallationCode"] = substr($this->contentExploded[$key + 1], 0, 10);

            return true;
        }

        return false;
    }

    private function extractBilling(string $value, int $key): bool
    {
        if (str_starts_with($value, "www.rge-rs.com.br")) {
            $rowKey = $key + 1;
            $row = explode(' ', $this->contentExploded[$rowKey]);

            $this->bill["Date"] = DateHelper::fromMonthYearPortuguese($row[1], true);
            $this->bill["DueDate"] = \DateTimeImmutable::createFromFormat('d/m/Y', $row[2]);
            $this->bill["Cost"] = Money::BRL((int) str_replace(',', '', $row[17]));

            return true;
        }

        return false;
    }

    private function extractNotices(string $value, int $key): bool
    {
        if (str_starts_with($value, "Lote Roteiro de leitura")) {
            $startRowKey = $key + 3;
            for ($endRowKey = $startRowKey; true; $endRowKey++) {
                if (str_starts_with($this->contentExploded[$endRowKey], $this->bill['Client']['Name'])) {
                    $endRowKey--;
                    break;
                }
            }

            $noticesText = $this->removeKeysBiggerThan($this->removeKeysSmallerThan($this->contentExploded, $startRowKey), $endRowKey);

            $this->bill["Notices"]["Text"] = implode(' ', $noticesText);

            return true;
        }

        return false;
    }

    private function extractSolarGeneration(string $value, int $key): bool
    {
        if (str_starts_with($value, "Participação na geração")) {
            $balanceKey = $key + 1;
            $nextMonthExpiringBalanceKey = $key + 2;
            $participationGeneration = (float) substr($value, 28, -1);

            $balance = substr($this->contentExploded[$balanceKey], 48, -5);
            $balance = $this->translateFloatFromBR($balance);

            $nextMonthExpiringBalance = substr($this->contentExploded[$nextMonthExpiringBalanceKey], 32, -4);
            $nextMonthExpiringBalance = $this->translateFloatFromBR($nextMonthExpiringBalance);

            $this->bill["SolarGeneration"]["ParticipationGeneration"] = $participationGeneration;
            $this->bill["SolarGeneration"]["Balance"] = $balance;
            $this->bill["SolarGeneration"]["NextMonthExpiringBalance"] = $nextMonthExpiringBalance;

            return true;
        }

        return false;
    }

    private function extractEnergyData(string $value, int $key)
    {
        if (str_contains($value, "Para consulta dos indicadores")) {
            $this->bill["EnergyData"] = [];
            $this->bill["EnergyData"]["EnergyConsumed"] = [];
            $this->bill["EnergyData"]["EnergyExcess"] = [];

            $energyConsumedExploded = explode(" ", $this->contentExploded[$key - 2]);
            $this->bill["EnergyData"]["EnergyConsumed"]["PreviousReading"] = (int) $energyConsumedExploded[5];
            $this->bill["EnergyData"]["EnergyConsumed"]["ActualReading"] = (int) $energyConsumedExploded[3];
            $this->bill["EnergyData"]["EnergyConsumed"]["MeterConstant"] = $this->translateFloatFromBR($energyConsumedExploded[8]);
            $this->bill["EnergyData"]["EnergyConsumed"]["Consumed"] = (int) $energyConsumedExploded[14];

            $energyExcessExploded = explode(" ", $this->contentExploded[$key - 1]);
            $this->bill["EnergyData"]["EnergyExcess"]["PreviousReading"] = (int) $energyExcessExploded[5];
            $this->bill["EnergyData"]["EnergyExcess"]["ActualReading"] = (int) $energyExcessExploded[3];
            $this->bill["EnergyData"]["EnergyExcess"]["MeterConstant"] = $this->translateFloatFromBR($energyExcessExploded[8]);
            $this->bill["EnergyData"]["EnergyExcess"]["Consumed"] = (int) $energyExcessExploded[14];

            $this->bill['PreviousReadingDate'] = \DateTime::createFromFormat('d/m/Y', substr($this->contentExploded[$key - 3], 12, 10));
            return true;
        }

        return false;
    }

    public static function translateFloatFromBR(string $float)
    {
        $floatWithoutStyling = str_replace(".", "", $float);
        $floatFormatted = str_replace(",", ".", $floatWithoutStyling);
        return (float) $floatFormatted;
    }

    private function removeKeysSmallerThan(array $array, int $x)
    {
        $filter = function (int $key) use ($x) {
            return !($key < $x);
        };

        return $this->filterArrayByKey($array, $filter);
    }

    private function removeKeysBiggerThan(array $array, int $x)
    {
        $filter = function (int $key) use ($x) {
            return !($key > $x);
        };

        return $this->filterArrayByKey($array, $filter);
    }

    private function filterArrayByKey(array $array, \Closure $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_KEY);
    }
}