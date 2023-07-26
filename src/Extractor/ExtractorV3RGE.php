<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

use NystronSolar\ElectricBillExtractor\Entity\Address;
use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Entity\Client;
use NystronSolar\ElectricBillExtractor\Entity\Dates;
use NystronSolar\ElectricBillExtractor\Entity\Establishment;
use NystronSolar\ElectricBillExtractor\Entity\SolarGeneration;
use NystronSolar\ElectricBillExtractor\Extractor;
use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;

final class ExtractorV3RGE extends Extractor
{
    /**
     * @psalm-suppress InvalidArrayOffset
     * @psalm-suppress ArgumentTypeCoercion
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
        }

        if (!$bill->isValid()) {
            return false;
        }

        return $bill;
    }
}
