<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

use Money\Money;
use NystronSolar\ElectricBillExtractor\Entity\Address;
use NystronSolar\ElectricBillExtractor\Entity\Bill;
use NystronSolar\ElectricBillExtractor\Entity\Client;
use NystronSolar\ElectricBillExtractor\Entity\Dates;
use NystronSolar\ElectricBillExtractor\Entity\Debit;
use NystronSolar\ElectricBillExtractor\Entity\Debits;
use NystronSolar\ElectricBillExtractor\Entity\Establishment;
use NystronSolar\ElectricBillExtractor\Entity\Power;
use NystronSolar\ElectricBillExtractor\Entity\Powers;
use NystronSolar\ElectricBillExtractor\Entity\SolarGeneration;
use NystronSolar\ElectricBillExtractor\Extractor;
use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;
use NystronSolar\ElectricBillExtractor\Helper\StringHelper;

final class ExtractorV2RGE extends Extractor
{
    /**
     * @psalm-suppress InvalidArrayOffset
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyFalseArgument
     * @psalm-suppress PossiblyUndefinedVariable
     * @psalm-suppress UnusedVariable
     * @psalm-suppress RedundantCondition
     * @psalm-suppress UndefinedVariable
     *
     * @todo Extract Discounts
     * @todo Extract Flags
     */
    protected function extractLoop(): Bill|false
    {
        $bill = new Bill();
        $contentArray = explode(PHP_EOL, $this->content);
        foreach ($contentArray as $key => $value) {
            if (str_starts_with($value, 'CLASSIFICAÇÃO: ')) {
                /**
                 * Example:
                 * TIMOTHY DA SILVA
                 * R FICTICIA 123
                 * CENTRO
                 * 12345-678 CIDADE - RS RG: 0123456789
                 * CLASSIFICAÇÃO: Convencional B1  Residencial - Bifásico 220 /  127 V.
                 */
                $addressRaw = $contentArray[$key - 1];
                $addressRaw = substr($addressRaw, 0, -15);
                $addressRawExploded = explode(' ', $addressRaw);
                $address = new Address(
                    trim($contentArray[$key - 3]),
                    $contentArray[$key - 2],
                    $addressRawExploded[0],
                    substr($addressRaw, 10, 6),
                    $addressRawExploded[array_key_last($addressRawExploded)],
                );

                $establishmentRaw = substr(trim($value), 17);
                $establishmentRawExploded = explode(' - ', $establishmentRaw);

                $establishment = new Establishment(
                    StringHelper::removeRepeatedWhitespace($establishmentRawExploded[0]),
                    explode(' ', trim($establishmentRawExploded[1]))[0]
                );

                $bill->client = new Client(trim($contentArray[$key - 4]), $address, $establishment);
            }

            if (str_starts_with($value, 'www.rge-rs.com.br')) {
                /**
                 * Example:
                 * www.rge-rs.com.br 012345678 INSTALAÇÃO
                 * 0123456789 ABR/2022 02/05/2022               99,16
                 * 
                 * www.rge-rs.com.br 012345678 INSTALAÇÃO
                 * 0123456789 JUL/2021 23/07/2021 **********
                 */
                $rawPriceLine = explode(' ', preg_replace('/\s+/', ' ', trim($contentArray[$key + 1])));
                $rawPrice = $rawPriceLine[array_key_last($rawPriceLine)];

                if (str_contains($rawPrice, '*')) {
                    $bill->price = Money::BRL('0');
                } else {
                    if (!$numericStringPrice = NumericHelper::brazilianNumberToNumericString($rawPrice)) {
                        return false;
                    }

                    if (!$price = NumericHelper::numericStringToMoney($numericStringPrice)) {
                        return false;
                    }

                    $bill->price = $price;
                }

                $bill->installationCode = $rawPriceLine[0];
                $bill->realPrice = $bill->price;
                $bill->lastMonthPrice = Money::BRL(0);
            }

            if (str_contains($value, 'Energia Ativa Fornecida - TUSD')) {
                /**
                 * Example:
                 * 0605 Energia Ativa Fornecida - TUSD ABR/22 283,000 kWh 0,50667845  143,39  143,39  25,00 35,85  107,54  1,08  5,01 
                 * 0601 Energia Ativa Fornecida - TE ABR/22 283,000 kWh 0,40219082  113,82  113,82  25,00 28,46  85,36  0,85  3,98 
                 * 0605 Energia Ativa Injetada TUSD ABR/22 283,000 kWh 0,38000000  107,54- 107,54- 1,08- 5,01-
                 * 0601 Energia Ativa Injetada TE ABR/22 283,000 kWh 0,40219082  113,82- 113,82- 25,00 28,46- 85,36- 0,85- 3,98-
                 * 0605 Custo de Disp. Energia TUSD ABR/22 50,000 kWh 0,50660000  25,33  25,33  25,00 6,33  19,00  0,19  0,89 
                 * 0601 Custo de Disp. Energia - TE ABR/22 50,000 kWh 0,40200000  20,10  20,10  25,00 5,03  15,07  0,15  0,70 
                 * Total Distribuidora 91,32 
                 * DÉBITOS DE OUTROS SERVIÇOS
                 * 0807 Contrib. Custeio IP-CIP  Municipal ABR/22 7,84 
                 */
                $tusdRaw = substr($value, 43);
                $tusdRaw = explode(' ', StringHelper::removeRepeatedWhitespace($tusdRaw));
                $tusd = new Debit(
                    NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($tusdRaw[3])),
                    'Tarifa de Uso do Sistema de Distribuição',
                    'TUSD',
                    NumericHelper::brazilianNumberToNumericString($tusdRaw[0])
                );

                $teRaw = substr($contentArray[$key + 1], 41);
                $teRaw = explode(' ', StringHelper::removeRepeatedWhitespace($teRaw));
                $te = new Debit(
                    NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($teRaw[3])),
                    'Tarifa de Energia',
                    'TE',
                    NumericHelper::brazilianNumberToNumericString($teRaw[0])
                );

                $cipRaw = trim(substr($contentArray[$this->findCipKey($contentArray, $key)], 47));
                $cip = new Debit(
                    NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($cipRaw)),
                    'Contribuição de Iluminação Pública',
                    'CIP',
                );

                $bill->debits = new Debits(
                    $tusd,
                    $te,
                    $cip
                );
            }

            if (str_contains($value, 'Taxa de Perda')) {
                /**
                 *                                                Nº Energia Leitura Leitura Fator Consumo Taxa de Perda Leitura
                 *  13/04/2022 15/03/2022 Multipl. [kWh] [%] Próximo Mês
                 * 01234567 Ativa  1953  1670   1,00      283 13/05/2022
                 * 01234567 Injetada  3130  2779   1,00      351
                 */

                $rawActiveLine = explode(' ', preg_replace('/\s+/', ' ', trim($contentArray[$key + 2])));
                $rawInjectedLine = explode(' ', preg_replace('/\s+/', ' ', trim($contentArray[$key + 3])));

                $bill->powers = new Powers(
                    new Power($rawActiveLine[2], $rawActiveLine[3], $rawActiveLine[5]),
                    new Power($rawInjectedLine[2], $rawInjectedLine[3], $rawInjectedLine[5]),
                );

                $dateFormatReset = '!d/m/Y';

                $rawLine = explode(' ', trim($contentArray[$key + 1]));
                $nextReadingDateStr = $rawActiveLine[6];
                $actualReadingDate = \DateTime::createFromFormat($dateFormatReset, $rawLine[0]);
                $previousReadingDate = \DateTime::createFromFormat($dateFormatReset, $rawLine[1]);
                $nextReadingDate = \DateTime::createFromFormat($dateFormatReset, $nextReadingDateStr);
                $date = \DateTime::createFromFormat('!m/Y', $actualReadingDate->format('m/Y'));

                $bill->dates = new Dates(
                    $actualReadingDate,
                    $previousReadingDate,
                    $nextReadingDate,
                    $date
                );
            }

            if (str_contains($value, 'Saldo em Energia da Instalação')) {
                /**
                 * Saldo em Energia da Instalação: Convencional 1.178,0000000000 kWh
                 * Saldo a expirar próximo mês: 0,0000000000 kWh
                 */

                /*
                 * Example:
                 * Saldo em Energia da Instalação:  Convencional 1.343,0000000000  kWh
                 * Saldo a expirar próximo mês:  0,0000000000 kWh
                 */
                $balance = StringHelper::removeRepeatedWhitespace(substr($value, 0, -5));
                $balance = explode(' ', $balance);
                $balance = $balance[array_key_last($balance)];
                $balance = NumericHelper::brazilianNumberToNumericString($balance);
                $toExpire = NumericHelper::brazilianNumberToNumericString(substr(StringHelper::removeRepeatedWhitespace($contentArray[$key + 1]), 32, -4));
                if (!$toExpire || !$balance) {
                    return false;
                }

                $bill->solarGeneration = new SolarGeneration(
                    $balance,
                    $toExpire
                );
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
        /**
         * Example:
         * 0807 Contrib. Custeio IP-CIP  Municipal ABR/22 7,84 
         */

        while (array_key_exists($currentKey, $contentArr)) {
            if (str_contains((string) $contentArr[$currentKey], 'Contrib. Custeio IP-CIP')) {
                return $currentKey;
            }

            ++$currentKey;
        }

        return false;
    }
}