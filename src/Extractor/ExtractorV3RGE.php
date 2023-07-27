<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

use NystronSolar\ElectricBillExtractor\Entity\Address;
use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Entity\Client;
use NystronSolar\ElectricBillExtractor\Entity\Dates;
use NystronSolar\ElectricBillExtractor\Entity\Debit;
use NystronSolar\ElectricBillExtractor\Entity\Debits;
use NystronSolar\ElectricBillExtractor\Entity\Establishment;
use NystronSolar\ElectricBillExtractor\Entity\SolarGeneration;
use NystronSolar\ElectricBillExtractor\Extractor;
use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;

final class ExtractorV3RGE extends Extractor
{
    /**
     * @psalm-suppress InvalidArrayOffset
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyFalseArgument
     *
     * @todo Extract RealPrice
     * @todo Extract EnergyConsumed
     * @todo Extract EnergyExcess
     * @todo Extract ConsumeTUSD
     * @todo Extract PriceTUSD
     * @todo Extract PriceTE
     * @todo Extract PriceIP
     * @todo Extract Discounts
     * @todo Extract Flags
     */
    public function extract(): Bill|false
    {
        $bill = new Bill();
        $contentArray = explode(PHP_EOL, $this->content);
        foreach ($contentArray as $key => $value) {
            if (str_starts_with($value, 'Inscrição Estadual')) {
                /**
                 * Example:
                 *  Inscrição Estadual: 124/0305939  - Inscrição no CNPJ:  02.016.440/0001-62
                 *  TIMOTHY DA SILVA
                 *  R FICTICIA 123
                 *  CENTRO
                 *  12345-678 CIDADE RS.
                 *  09 ABCDE012-00000123 12345678 1/ 1 19/05/2023 14/06/2023 01/06/2023
                 *   Classificação:   Convencional B1 Residencial Tipo de Fornecimento:
                 *   Bifásico.
                 */
                $address = new Address(
                    trim($contentArray[$key + 2]),
                    $contentArray[$key + 3],
                    substr($contentArray[$key + 4], 0, 9),
                    substr($contentArray[$key + 4], 10, -3),
                    substr($contentArray[$key + 4], -2),
                );

                $establishment = new Establishment(
                    substr(trim(substr(trim($contentArray[$key + 6]), 16)), 0, -22),
                    trim($contentArray[$key + 7])
                );

                $bill->client = new Client($contentArray[$key + 1], $address, $establishment);
            }

            if (str_starts_with($value, 'TENSÃO NOMINAL EM VOLTS')) {
                /**
                 * Example:
                 * TENSÃO NOMINAL EM VOLTS Disp.:   220 Lim. mín.:  202 Lim. máx.:  231
                 * 16/05/2023 14/04/2023 32
                 * Próxima leitura    14/06/2023TIMOTHY DA SILVA.
                 */
                $dateFormatReset = '!d/m/Y';
                $monthFormatReset = '!m/y';
                $actualReadingDate = \DateTime::createFromFormat($dateFormatReset, substr($contentArray[$key + 1], 0, 10));

                $bill->dates = new Dates(
                    $actualReadingDate,
                    \DateTime::createFromFormat($dateFormatReset, substr($contentArray[$key + 1], 11, 10)),
                    \DateTime::createFromFormat($dateFormatReset, substr(trim(substr($contentArray[$key + 2], 16)), 0, 10)),
                    \DateTime::createFromFormat($monthFormatReset, $actualReadingDate->format('m/y'))
                );
            }

            if (str_starts_with($value, 'NOTA FISCAL Nº')) {
                /*
                 * Example:
                 * CPF: ******.123-** 0123456789
                 * NOTA FISCAL Nº  123456789  - SÉRIE  0  / DATA DE EMISSÃO:
                 */
                $bill->installationCode = substr((string) $contentArray[$key - 1], -10);
            }

            if (str_starts_with($value, 'Saldo em Energia da Instalação')) {
                /*
                 * Example:
                 * Saldo em Energia da Instalação:  Convencional 1.343,0000000000  kWh
                 * Saldo a expirar próximo mês:  0,0000000000 kWh
                 */
                $balance = substr($value, 0, -5);
                $balance = explode(' ', $balance);
                $balance = $balance[array_key_last($balance)];
                $balance = NumericHelper::brazilianNumberToNumericString($balance);
                $toExpire = NumericHelper::brazilianNumberToNumericString(substr($contentArray[$key + 1], 32, -4));
                if (!$toExpire || !$balance) {
                    return false;
                }

                $bill->solarGeneration = new SolarGeneration(
                    $balance,
                    $toExpire
                );
            }

            if (str_starts_with($value, 'Protocolo de autorização')) {
                /*
                 * Example:
                 * Protocolo de autorização:  0123456789012345 -16.05.2023 às  22:16:12
                 * MAI/2023 01/06/2023 R$ 88,78
                 */
                if (!$numericStringPrice = NumericHelper::brazilianNumberToNumericString(substr($contentArray[$key + 1], 23))) {
                    return false;
                }

                if (!$price = NumericHelper::numericStringToMoney($numericStringPrice)) {
                    return false;
                }

                $bill->price = $price;
            }

            if (str_starts_with($value, '    Consumo Uso Sistema [KWh]-TUSD')) {
                /*
                 * Example:
                 *     Consumo Uso Sistema [KWh]-TUSD  MAI/23 kWh 426,0000   0,43754000  0,55525822  236,54  236,54  17,00   40,21  1,79  8,15
                 * Consumo - TE MAI/23 kWh 426,0000   0,26162000  0,33199531  141,43  141,43  17,00   24,04  1,07  4,87
                 * Energia Ativa Injetada TUSD  MAI/23 kWh 278,0000   0,43754000  0,46089929  128,13- 0.00   1,17- 5,32-
                 * Energia Ativa Injetada TE  MAI/23 kWh 278,0000   0,26162000  0,33201439  92,30- 92,30- 17,00   15,69- 0,70- 3,18-
                 * Energ Atv Inj. mUC mPT - TUSD  DEZ/21 kWh 98,0000   0,43754000  0,46081633  45,16- 0.00   0,41- 1,87-
                 * Energ Atv Inj. mUC mPT - TE  DEZ/21 kWh 98,0000   0,26162000  0,33204082  32,54- 32,54- 17,00   5,53- 0,25- 1,12-
                 * Total Distribuidora       79,84
                 * DÉBITOS DE OUTROS SERVIÇOS
                 * Contribuição Custeio IP-CIP  MAI/23       8,94
                 */
                $cipKey = $this->findCipKey($contentArray, $key);
                if (!$cipKey) {
                    return false;
                }

                try {
                    $tusdAct = new Debit(
                        NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($value, 47)), fn (string $v) => !empty($v)))[3])),
                        'Tarifa de Uso do Sistema de Distribuição Ativa',
                        'TUSD Ati',
                        NumericHelper::brazilianNumberToNumericString(explode(' ', substr($value, 47))[0] ?? '0.0')
                    );

                    $tusdInj = new Debit(
                        NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($contentArray[$key + 2], 40)), fn (string $v) => !empty($v)))[3], negativeOnEnd: true), negativeOnEnd: true),
                        'Tarifa de Uso do Sistema de Distribuição Injetada',
                        'TUSD Inj',
                        NumericHelper::brazilianNumberToNumericString(explode(' ', substr($contentArray[$key + 2], 40))[0] ?? '0.0')
                    );

                    $te = new Debit(
                        NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($contentArray[$key + 1], 24)), fn (string $v) => !empty($v)))[3])),
                        'Tarifa de Energia',
                        'TE',
                        NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($contentArray[$key + 1], 24)), fn (string $v) => !empty($v)))[0])
                    );

                    $cip = new Debit(
                        NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(trim(substr($contentArray[$cipKey], 37)))),
                        'Contribuição de Iluminação Pública',
                        'CIP'
                    );
                } catch (\TypeError $e) {
                    return false;
                }

                $bill->debits = new Debits($tusdAct, $tusdInj, $te, $cip);
            }
        }

        if (!$bill->isValid()) {
            return false;
        }

        return $bill;
    }

    /**
     * @param array<int, mixed> $contentArr
     */
    private function findCipKey(array $contentArr, int $currentKey = 0): int|false
    {
        while (array_key_exists($currentKey, $contentArr)) {
            if (str_starts_with((string) $contentArr[$currentKey], 'Contribuição Custeio IP-CIP')) {
                return $currentKey;
            }

            ++$currentKey;
        }

        return false;
    }
}
