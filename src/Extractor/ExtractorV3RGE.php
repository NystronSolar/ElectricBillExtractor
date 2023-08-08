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

final class ExtractorV3RGE extends Extractor
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
            if (str_starts_with($value, 'Inscrição Estadual')) {
                /**
                 * Example:
                 * Inscrição Estadual: 124/0305939  - Inscrição no CNPJ:  02.016.440/0001-62
                 * TIMOTHY DA SILVA
                 * R FICTICIA 123
                 * CENTRO
                 * 12345-678 CIDADE RS.
                 * 09 ABCDE012-00000123 12345678 1/ 1 19/05/2023 14/06/2023 01/06/2023
                 *  Classificação:   Convencional B1 Residencial Tipo de Fornecimento:
                 * Bifásico.
                 * 
                 *  Classificação:   Convencional B3 Comercial  Outros Serviços
                 * Atividades Tipo de Fornecimento:
                 * Monofásico
                 */
                $address = new Address(
                    trim($contentArray[$key + 2]),
                    $contentArray[$key + 3],
                    substr($contentArray[$key + 4], 0, 9),
                    substr($contentArray[$key + 4], 10, -3),
                    substr($contentArray[$key + 4], -2),
                );

                $classification = trim(substr(trim($contentArray[$key + 6]), 16));
                $supplyType = trim($contentArray[$key + 7]);
                if (!str_contains($contentArray[$key + 6], 'Tipo de Fornecimento:')) {
                    $classification .= ' ' . $contentArray[$key + 7];
                    $supplyType = trim($contentArray[$key + 8]);
                }
                $classification = StringHelper::removeRepeatedWhitespace(substr($classification, 0, -22));

                $establishment = new Establishment($classification, $supplyType);

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
                 *
                 * Protocolo de autorização:  0123456789012345 -15.07.2022 às  02:47:19
                 * JUL/2022 01/08/2022 R$ **********
                 */

                $rawPrice = substr($contentArray[$key + 1], 23);

                // Bill has no price to pay
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
            }

            if (str_contains($value, 'Consumo Uso Sistema [KWh]-TUSD')) {
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

                $cipKey = $this->findCipKey($contentArray, $bill, $key);

                if (!$cipKey) {
                    return false;
                }

                try {
                    $tusdPrice = NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($value, 47)), fn(string $v) => !empty($v)))[3]));
                    if (!$tusdPrice) {
                        return false;
                    }

                    $tusdKWhAmount = NumericHelper::brazilianNumberToNumericString(explode(' ', substr(trim($value), 43))[0] ?? '0.0');
                    if (!$tusdKWhAmount) {
                        return false;
                    }

                    if (isset($tusd) && !is_null($tusd)) {
                        /** @var Debit $tusd */
                        $tusdPrice = $tusdPrice->add($tusd->price);
                        $tusdKWhAmount = bcadd($tusd->kWhAmount ?? '0.0', $tusdKWhAmount, 1);
                    }

                    $tusd = new Debit(
                        $tusdPrice,
                        'Tarifa de Uso do Sistema de Distribuição',
                        'TUSD',
                        $tusdKWhAmount
                    );

                    $cip = new Debit(
                        NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(trim(substr($contentArray[$cipKey], 37)))),
                        'Contribuição de Iluminação Pública',
                        'CIP'
                    );
                } catch (\TypeError $e) {
                    return false;
                }

                $actualKey = $cipKey;
                while (is_null($bill->realPrice)) {
                    $actualKey = $actualKey + 1;
                    $actualValue = $contentArray[$actualKey];
                    if (empty(trim($actualValue))) {
                        continue;
                    }

                    $realPrice = NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(explode(' ', trim($actualValue))[0]));
                    if (!$realPrice) {
                        return false;
                    }

                    $bill->realPrice = $realPrice;
                }
            }

            if (str_contains($value, 'Consumo - TE')) {
                $tePrice = NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($value, 24)), fn(string $v) => !empty($v)))[3]));
                if (!$tePrice) {
                    return false;
                }

                $teKWhAmount = NumericHelper::brazilianNumberToNumericString(array_values(array_filter(explode(' ', substr($value, 24)), fn(string $v) => !empty($v)))[0]);
                if (!$teKWhAmount) {
                    return false;
                }

                if (isset($te) && !is_null($te)) {
                    /** @var Debit $te */
                    $tePrice = $tePrice->add($te->price);
                    $teKWhAmount = bcadd($te->kWhAmount ?? '0.0', $teKWhAmount, 1);
                }

                $te = new Debit(
                    $tePrice,
                    'Tarifa de Energia',
                    'TE',
                    $teKWhAmount
                );

                /**
                 * @var Debit $tusd
                 * @var Debit $cip
                 */
                $bill->debits = new Debits($tusd, $te, $cip);
            }

            if (str_starts_with($value, 'Conta Mês Anterior')) {
                /**
                 * Example:
                 * Conta Mês Anterior JAN/23       39,29.
                 */
                $raw = trim(substr($value, 26));
                $lastMonthPrice = NumericHelper::numericStringToMoney(NumericHelper::brazilianNumberToNumericString($raw));
                if (!$lastMonthPrice) {
                    return false;
                }

                $bill->lastMonthPrice = $lastMonthPrice;
                $bill->realPrice = $bill->realPrice?->subtract($bill->lastMonthPrice);
            }

            if (str_contains($value, 'Energia Ativa-kWh')) {
                /*
                 * Example:
                 * 12345678 Energia Ativa-kWh único 6306 6732 1,00 426
                 * 12345678 Energia Injetada único 7481 7759 1,00 278 Verde        16 Dias
                 */
                $powerActiveExploded = explode(' ', $value);
                $powerActive = new Power(
                    $powerActiveExploded[5],
                    $powerActiveExploded[4],
                    preg_replace('/\D/', '', $powerActiveExploded[7] ?? $powerActiveExploded[6]),
                );

                $powerInjectedExploded = explode(' ', $contentArray[$key + 1]);
                $powerInjected = $powerInjectedExploded[2] !== 'Injetada' ? null : new Power(
                    $powerInjectedExploded[5],
                    $powerInjectedExploded[4],
                    preg_replace('/\D/', '', $powerInjectedExploded[7] ?? $powerInjectedExploded[6]),
                );

                $bill->powers = new Powers($powerActive, $powerInjected);
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
    private function findCipKey(array $contentArr, Bill $bill, int $currentKey = 0): int|false
    {
        $date = $bill->dates?->date;
        if (is_null($date)) {
            return false;
        }

        $dateFormatter = new \IntlDateFormatter('pt_br', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
        $dateStyled = strtoupper(str_replace('.', '', $dateFormatter->format($date)));
        while (array_key_exists($currentKey, $contentArr)) {
            if (str_starts_with((string) $contentArr[$currentKey], 'Contribuição Custeio IP-CIP') && str_contains((string) $contentArr[$currentKey], $dateStyled)) {
                return $currentKey;
            }

            ++$currentKey;
        }

        return false;
    }
}