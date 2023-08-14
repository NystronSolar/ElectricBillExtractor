<?php

namespace NystronSolar\ElectricBillExtractor\Extractor;

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
use NystronSolar\ElectricBillExtractor\Helper\DateHelper;
use NystronSolar\ElectricBillExtractor\Helper\NumericHelper;
use NystronSolar\ElectricBillExtractor\Helper\StringHelper;
use TheDevick\PreciseMoney\Money;

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
                 * 
                 * TIMOTHY DA SILVA
                 * R FICTICIA 123
                 * CENTRO
                 * 12345-678 CIDADE - RS CPF: 123.456.789-01
                 * CLASSIFICAÇÃO: Convencional B1  Residencial - Bifásico 220 /  127 V.
                 */
                $actualKey = str_contains((string) $contentArray[$key - 1], 'INSC.EST') ? $key - 1 : $key;


                $addressRaw = (string) $contentArray[$actualKey - 1];
                $addressRaw = substr($addressRaw, 0, str_contains($addressRaw, 'CPF') ? -20 : -15);
                $addressRawExploded = explode(' ', $addressRaw);
                $address = new Address(
                    trim((string) $contentArray[$actualKey - 3]),
                    (string) $contentArray[$actualKey - 2],
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

                $bill->client = new Client(trim((string) $contentArray[$actualKey - 4]), $address, $establishment);
            }

            if (str_starts_with($value, 'www.rge-rs.com.br')) {
                /**
                 * Example:
                 * www.rge-rs.com.br 012345678 INSTALAÇÃO
                 * 0123456789 ABR/2022 02/05/2022               99,16.
                 *
                 * www.rge-rs.com.br 012345678 INSTALAÇÃO
                 * 0123456789 JUL/2021 23/07/2021 **********
                 */
                $rawPriceLine = explode(' ', preg_replace('/\s+/', ' ', trim($contentArray[$key + 1])));
                $rawPrice = $rawPriceLine[array_key_last($rawPriceLine)];

                if (str_contains($rawPrice, '*')) {
                    $bill->price = new Money('0');
                } else {
                    if (!$numericStringPrice = NumericHelper::brazilianNumberToNumericString($rawPrice)) {
                        return false;
                    }

                    if (!$price = NumericHelper::numericStringToMoney($numericStringPrice)) {
                        return false;
                    }

                    $bill->price = $price;
                }

                $billYear = substr($rawPriceLine[1], 4);
                $billMonth = DateHelper::getShortMonthNumberPtBr(substr($rawPriceLine[1], 0, 3));
                $date = \DateTimeImmutable::createFromFormat('!n/Y', "$billMonth/$billYear");
                if (!$date) {
                    return false;
                }

                $bill->date = $date;
                $bill->installationCode = $rawPriceLine[0];
            }

            if (
                str_contains($value, 'Energia Ativa Fornecida - TUSD') ||
                str_contains($value, 'Consumo Uso Sistema [KWh]-TUSD') ||
                $modifier = str_contains($value, 'Custo Disp Uso Sistema TUSD')
            ) {
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
                 * 0807 Contrib. Custeio IP-CIP  Municipal ABR/22 7,84.
                 *
                 * 0605 Consumo Uso Sistema [KWh]-TUSD JAN/19 1.654,000 kWh 0,38561669  637,81  637,81  30,00 191,34  637,81  5,68  26,47
                 * 0601 Consumo - TE JAN/19 1.654,000 kWh 0,45692866  755,76  755,76  30,00 226,73  755,76  6,73  31,36
                 * Total Distribuidora 1.393,57
                 * DÉBITOS DE OUTROS SERVIÇOS
                 * 0807 Contrib. Custeio IP-CIP  Municipal JAN/19 36,21
                 *
                 * 0605 Custo Disp Uso Sistema TUSD DEZ/21 50,000 kWh 0,42800000  21,40  21,40  12,00 2,57  18,83  0,16  0,75
                 * 0601 Disp Sistema-TE DEZ/21 50,000 kWh 0,33980000  16,99  16,99  12,00 2,04  14,95  0,13  0,60
                 */
                $hasGeneration = str_contains($value, 'Energia Ativa');

                $tusdRaw = substr($value, $modifier ? 39 : 43);
                $tusdRaw = explode(' ', StringHelper::removeRepeatedWhitespace($tusdRaw));
                $tusd = new Debit(
                    NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($tusdRaw[3])),
                    'Tarifa de Uso do Sistema de Distribuição',
                    'TUSD',
                    NumericHelper::brazilianNumberToNumericString($tusdRaw[0])
                );

                $teRaw = substr($contentArray[$key + 1], $hasGeneration ? 41 : ($modifier ? 28 : 25));
                $teRaw = explode(' ', StringHelper::removeRepeatedWhitespace($teRaw));
                $te = new Debit(
                    NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($teRaw[3])),
                    'Tarifa de Energia',
                    'TE',
                    NumericHelper::brazilianNumberToNumericString($teRaw[0])
                );

                $cipKey = $this->findCipKey($contentArray, $key);
                $cipRaw = trim(substr($contentArray[$cipKey], 47));
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

                /**
                 * Example:
                 * Total Consolidado             99,16  198,86  49,72  41,60  0,41  1,95  Bandeiras
                 */
                $actualKey = $cipKey;
                while (is_null($bill->realPrice)) {
                    $actualKey = $actualKey + 1;
                    $actualValue = $contentArray[$actualKey];
                    if (empty(trim($actualValue))) {
                        continue;
                    }

                    $realPrice = NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(explode(' ', trim(substr($actualValue, 17)))[0]));
                    if (!$realPrice) {
                        return false;
                    }

                    $bill->realPrice = $realPrice;
                }
            }

            if (str_contains($value, 'Conta do mês')) {
                /**
                 * Example:
                 * 0699 Conta do mês DEZ/21 46,87 
                 * 0807 Conta do mês DEZ/21 1,17 
                 * 0804 Conta do mês DEZ/21 0,35 
                 * 0805 Conta do mês DEZ/21 7,69 
                 */
                $raw = trim(substr($value, 26));
                $currentLastMonthPrice = NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($raw));
                if (!$currentLastMonthPrice) {
                    return false;
                }

                $lastMonthPrice = $currentLastMonthPrice->addMoney($bill->lastMonthPrice ?? new Money('0'));

                $bill->lastMonthPrice = $lastMonthPrice;
                $bill->realPrice = $bill->realPrice?->subMoney($currentLastMonthPrice);
            }

            if (str_contains($value, 'Taxa de Perda')) {
                /**
                 *                                                Nº Energia Leitura Leitura Fator Consumo Taxa de Perda Leitura
                 *  13/04/2022 15/03/2022 Multipl. [kWh] [%] Próximo Mês
                 * 01234567 Ativa  1953  1670   1,00      283 13/05/2022
                 * 01234567 Injetada  3130  2779   1,00      351.
                 */
                $rawActiveLine = explode(' ', preg_replace('/\s+/', ' ', trim($contentArray[$key + 2])));
                $rawInjectedLine = explode(' ', preg_replace('/\s+/', ' ', trim($contentArray[$key + 3])));

                $injectedExists = 'Injetada' === $rawInjectedLine[1];

                $bill->powers = new Powers(
                    new Power(
                        NumericHelper::brazilianNumberToNumericString($rawActiveLine[2]),
                        NumericHelper::brazilianNumberToNumericString($rawActiveLine[3]),
                        NumericHelper::brazilianNumberToNumericString($rawActiveLine[5])
                    ),
                    $injectedExists ? new Power(
                        NumericHelper::brazilianNumberToNumericString($rawInjectedLine[2]),
                        NumericHelper::brazilianNumberToNumericString($rawInjectedLine[3]),
                        NumericHelper::brazilianNumberToNumericString($rawInjectedLine[5])
                    ) : null
                );

                $dateFormatReset = '!d/m/Y';

                $rawLine = explode(' ', trim($contentArray[$key + 1]));
                $nextReadingDateStr = $rawActiveLine[6];
                $actualReadingDate = \DateTime::createFromFormat($dateFormatReset, $rawLine[0]);
                $previousReadingDate = \DateTime::createFromFormat($dateFormatReset, $rawLine[1]);
                $nextReadingDate = \DateTime::createFromFormat($dateFormatReset, $nextReadingDateStr);

                $bill->dates = new Dates(
                    $actualReadingDate,
                    $previousReadingDate,
                    $nextReadingDate,
                );
            }

            if (str_contains($value, 'Saldo em Energia da Instalação')) {
                /**
                 * Saldo em Energia da Instalação: Convencional 1.178,0000000000 kWh
                 * Saldo a expirar próximo mês: 0,0000000000 kWh.
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
            dd($bill);
            return false;
        }

        return $bill;
    }

    /**
     * @param array<int, mixed> $contentArr
     */
    private function findCipKey(array $contentArr, int $currentKey = 0): int|false
    {
        /*
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