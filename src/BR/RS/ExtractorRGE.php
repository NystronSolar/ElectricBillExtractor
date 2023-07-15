<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

use Closure;
use DateTime;
use DateTimeImmutable;
use Money\Money;
use NystronSolar\ElectricBillExtractor\Extractor;
use NystronSolar\ElectricBillExtractor\Helper\DateHelper;

class ExtractorRGE extends Extractor
{
    protected function extract(): array
    {
        foreach ($this->contentExploded as $key => $value) {
            $this->extractClient($value, $key);
            $this->extractHeader($value, $key);
            $this->extractBuilding($value, $key);
            $this->extractReadingDates($value, $key);
            $this->extractInstallationCode($value);
            $this->extractBilling($value, $key);
            $this->extractNotices($value, $key);
            $this->extractSolarGeneration($value, $key);
            $this->extractTaxTUSD($value);
            $this->extractTaxTE($value);
            $this->extractTaxIP($value);
            $this->extractLastMonthCosts($value, $key);
            $this->extractRealCost($value, $key);
            // $this->extractTaxDiscounts($value, $key);
            $this->extractEnergyData($value, $key);
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

    private function extractReadingDates(string $value, int $key): bool
    {
        if (str_starts_with($value, " Classificação:")) {
            $row = $key + 3;

            $valuesArray = explode(' ', $this->contentExploded[$row]);

            $actualReadingDate = DateTime::createFromFormat('d/m/Y', $valuesArray[0]);
            $previousReadingDate = DateTime::createFromFormat('d/m/Y', $valuesArray[1]);
            $totalDays = (int) $valuesArray[2];

            $this->bill["ActualReadingDate"] = $actualReadingDate;
            $this->bill["PreviousReadingDate"] = $previousReadingDate;
            $this->bill["TotalDays"] = $totalDays;

            return true;
        }

        return false;
    }

    private function extractInstallationCode(string $value): bool
    {
        if (str_starts_with($value, "CPF:")) {
            $this->bill["InstallationCode"] = substr($value, 19);

            return true;
        }

        return false;
    }

    private function extractBilling(string $value, int $key): bool
    {
        if (str_starts_with($value, "Protocolo")) {
            $rowKey = $key + 1;
            $row = $this->contentExploded[$rowKey];

            $this->bill["Date"] = DateHelper::fromMonthYearPortuguese(substr($row, 0, 8), true);
            $this->bill["Cost"] = Money::BRL((int) str_replace(',', '', str_replace('*', '0', substr($row, 23))));

            return true;
        }

        return false;
    }

    private function extractNotices(string $value, int $key): bool
    {
        if (str_starts_with($value, "Protocolo")) {
            $startRowKey = $key + 2;
            for ($endRowKey = $startRowKey; true; $endRowKey++) {
                if ($this->contentExploded[$endRowKey] == " ") {
                    $endRowKey--;
                    break;
                }
            }

            $noticesText = $this->removeKeysBiggerThan($this->removeKeysSmallerThan($this->contentExploded, $startRowKey), $endRowKey);

            $this->bill["Notices"]["Text"] = implode('\\n', $noticesText);

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

    private function extractTaxTUSD(string $value)
    {
        if (str_starts_with($value, "Consumo Uso Sistema")) {
            $lineRaw = preg_replace('/\s+/', ' ', substr($value, 43));
            $lineExplode = explode(' ', $lineRaw);
            $this->bill["Taxes"] = [];
            $this->bill["Taxes"]["TUSD"] = [];
            $this->bill["Taxes"]["TUSD"]["Consumed"] = $this->translateFloatFromBR($lineExplode[0]);
            $this->bill["Taxes"]["TUSD"]["TotalPrice"] = Money::BRL((int) str_replace('.', '', $this->translateFloatFromBR($lineExplode[3])));

            return true;
        }

        return false;
    }

    private function extractTaxTE(string $value)
    {
        if (str_starts_with($value, "Consumo - TE")) {
            $lineRaw = preg_replace('/\s+/', ' ', substr($value, 43));
            $lineExplode = explode(' ', $lineRaw);
            $this->bill["Taxes"]["TE"] = [];
            $this->bill["Taxes"]["TE"]["TotalPrice"] = Money::BRL((int) str_replace('.', '', $this->translateFloatFromBR($lineExplode[3])));

            return true;
        }

        return false;
    }

    private function extractTaxIP(string $value)
    {
        if (str_starts_with($value, "Contribuição Custeio IP")) {
            $lineRaw = preg_replace('/\s+/', ' ', substr($value, 43));
            $lineExplode = explode(' ', $lineRaw);
            $this->bill["Taxes"]["IP"] = [];
            $this->bill["Taxes"]["IP"]["TotalPrice"] = Money::BRL((int) str_replace('.', '', $this->translateFloatFromBR($lineExplode[1])));

            return true;
        }

        return false;
    }

    private function extractLastMonthCosts(string $value, int $key)
    {
        if (str_starts_with($value, "Consumo Uso Sistema")) {
            $lastMonthCost = Money::BRL(0);
            for ($i = $key; true; $i++) {
                $actualMonth = (int) $this->bill["Date"]->format('n');
                $lastMonthInt = $actualMonth - 1;
                $lastMonthInt = $lastMonth < 1 ? 12 : $lastMonth;
                $lastMonth = DateHelper::getPortugueseMonths(true)[$lastMonthInt];
                $actualValue = $this->contentExploded[$i];

                if (preg_match('/^[0-9]{1}/', trim($actualValue))) {
                    break;
                }

                if (str_contains($actualValue, $lastMonth)) {
                    $arr = explode(' ', trim($actualValue));
                    $lastMonthCost = $lastMonthCost->add(Money::BRL((int) str_replace(',', '', end($arr))));
                }
            }
            $this->bill['LastMonthCost'] = $lastMonthCost;
            return true;
        }

        return false;
    }

    private function extractRealCost(string $value, int $key)
    {
        if (str_starts_with($value, "DÉBITOS DE OUTROS SERVIÇOS")) {
            for ($i = $key; true; $i++) {
                $actualValue = $this->contentExploded[$i];
                if (preg_match('/^[0-9]{1}/', trim($actualValue))) {
                    $arr = explode(' ', trim($actualValue));
                    $actualCost = Money::BRL((int) str_replace(',', '', $arr[array_key_first($arr)]));
                    $this->bill['RealCost'] = $actualCost->subtract($this->bill['LastMonthCost'] ?? Money::BRL(0));
                    return true;
                }
            }
        }

        return false;
    }

    private function extractEnergyData(string $value, int $key)
    {
        if (str_contains($value, "Energia Ativa-kWh")) {
            $this->bill["EnergyData"] = [];
            $this->bill["EnergyData"]["EnergyConsumed"] = [];
            $this->bill["EnergyData"]["EnergyExcess"] = [];

            $energyConsumedExploded = explode(" ", $value);
            $this->bill["EnergyData"]["EnergyConsumed"]["Timetables"] = $energyConsumedExploded[3];
            $this->bill["EnergyData"]["EnergyConsumed"]["PreviousReading"] = (int) $energyConsumedExploded[4];
            $this->bill["EnergyData"]["EnergyConsumed"]["ActualReading"] = (int) $energyConsumedExploded[5];
            $this->bill["EnergyData"]["EnergyConsumed"]["MeterConstant"] = $this->translateFloatFromBR($energyConsumedExploded[6]);
            $this->bill["EnergyData"]["EnergyConsumed"]["Consumed"] = (int) $energyConsumedExploded[7];

            $energyExcessExploded = explode(" ", $this->contentExploded[$key + 1]);
            $this->bill["EnergyData"]["EnergyExcess"]["Timetables"] = $energyExcessExploded[3];
            $this->bill["EnergyData"]["EnergyExcess"]["PreviousReading"] = (int) $energyExcessExploded[4];
            $this->bill["EnergyData"]["EnergyExcess"]["ActualReading"] = (int) $energyExcessExploded[5];
            $this->bill["EnergyData"]["EnergyExcess"]["MeterConstant"] = $this->translateFloatFromBR($energyExcessExploded[6]);
            $this->bill["EnergyData"]["EnergyExcess"]["Consumed"] = (int) $energyExcessExploded[7];

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

    private function filterArrayByKey(array $array, Closure $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_KEY);
    }
}