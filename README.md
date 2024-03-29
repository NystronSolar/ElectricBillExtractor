# Electric Bill Extractor

[![NystronSolarBadge](https://img.shields.io/badge/⚡%20Powered%20By-Nystron%20Solar-yellow?style=for-the-badge)](https://github.com/NystronSolar)
[![PHPUnitBadge](https://img.shields.io/badge/✓%20PHPUnit-Tests-blue?style=for-the-badge)](https://phpunit.de/)
[![PHPBadge](https://img.shields.io/badge/🐘%20PHP-8.2-steelblue?style=for-the-badge)](https://php.net/)
[![PsalmBadge](https://img.shields.io/badge/📌%20Psalm-Static%20Analysis-red?style=for-the-badge)](https://psalm.dev/)
[![SemanticReleaseBadge](https://img.shields.io/badge/semantic--release-angular-e10079?logo=semantic-release&style=for-the-badge)](https://github.com/semantic-release/semantic-release)

![Electric Bill Extractor](https://github.com/NystronSolar/ElectricBillExtractor/assets/71853418/1b7a1590-c2ec-4e01-9a04-f96171a05350)

Logo made with [Canva](https://canva.com)

## Getting Started

First, instal the package with composer:

```bash
composer require nystronsolar/electric-bill-extractor
```

Next, extract the data from an electric bill:

```php
<?php

use NystronSolar\ElectricBillExtractor\ExtractorFactory;

require_once __DIR__.'/vendor/autoload.php';

$bill = ExtractorFactory::extractFromFile('bill.pdf');
```

## How the library works?

### The Extractors

Each type of bill needs to have a custom extractor. Each extractor extends the [Extractor Abstract Class](src/Extractor.php), which when instantiating, you need to provide the parsed content of an bill. After it, you can extract its content by using the `extract()` method, that it will returns a bill object or false in case of error.

#### List of all Extractors

| Name             | Class                                                                                             | Example                                                                                               |
|------------------|---------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------|
| Extractor V2 RGE | [`NystronSolar\ElectricBillExtractor\Extractor\ExtractorV2RGE`](src/Extractor/ExtractorV2RGE.php) | [From Official RGE Website](https://www.rge-rs.com.br/sites/rge-rs/files/2022-05/conta-rge.png)       |
| Extractor V3 RGE | [`NystronSolar\ElectricBillExtractor\Extractor\ExtractorV3RGE`](src/Extractor/ExtractorV3RGE.php) | [From Official RGE Website](https://www.rge-rs.com.br/sites/rge-rs/files/2022-04/novaconta-rge_0.png) |

### The Extractor Factory

> [`NystronSolar\ElectricBillExtractor\ExtractorFactory`](src/ExtractorFactory.php)

The Extractor Factory is a class that helps creating extractors

With the Extractor Factory, you can:

- Identify the extractor class from an string content or file path with the `identifyExtractorClassFromContent()` or `identifyExtractorClassFromFile()` methods
- Instantiate the extractor class from an string content or file path with the `instantiateExtractorClassFromFile()` and `instantiateExtractorClassFromFile()` methods
- Extract the bill from an string content or file path with the `extractFromFile()` and `extractFromFile()` methods

```php
<?php

use NystronSolar\ElectricBillExtractor\ExtractorFactory;

require_once __DIR__.'/vendor/autoload.php';

// Returns an class-string of an extractor that can be used or false
ExtractorFactory::identifyExtractorClassFromFile('bill.pdf');
ExtractorFactory::identifyExtractorClassFromContent('An parsed bill content');

// Return an new extractor instance or false
ExtractorFactory::instantiateExtractorFromFile('bill.pdf');
ExtractorFactory::instantiateExtractorFromContent('An parsed bill content');

// Extract an Bill - Return an bill object or false
ExtractorFactory::extractFromFile('bill.pdf');
ExtractorFactory::extractFromContent('An parsed bill content');
```

## Tests

The tests for the Electric Bill Extractor are pretty!

### Content

To test an File Extractor library, we need to have many files to extract and check the results. And that's because, in the Electric Bill Extractor, under the `tests/Content/bills/` folder, you can find many `.txt` files that represents many fake bills, following the pattern for each extractor.

And to compare the results by the extractor and the actual bills, you can find `.json` files in `tests/Content/expected/` that when the tests run, it will compare the expected (json) with the actual bills (txt)

#### Why not use PDF files?

In most real-cases, projects will extract the data from an PDF file. In that approach, the Electric Bill Extractor Factory will parse the PDF file into text, using the [https://github.com/smalot/pdfparser](smalot/pdfparser).

After the parsing, the library will use the text returned from the Parser and extract all the data for the bill.

But in tests, we can skip all the parsing. And that's because we use `.txt` instead of `.pdf`

### The Test Case

Since the extractor have this "strange" way to assert the classes, we built the [Extractor Test Case](tests/TestCase/ExtractorTestCase.php), which is a Custom PHPUnit Test Case. This test case have an `assertByContentFolder` method, which you need to provide the folder name and the extractor class.

### Uploading a new Test Bill

The bills under `tests/Content/bills` are usually real bills, but with some values changed. If you found an error with your bill and wan't to upload it to the repository, it's extremely important to change some values, such as real name, CPF, installation code, etc, since this project is Open Source, so **anyone** can see the bills.

You can use the command `composer upload-bill` to help you adding a new bill, but you still have to remove all the secrets and fill the JSON expected file.

Here you can see all the values that must have to be changed in all bills.

#### RGE V3

> Values are named in Portuguese, in order.

- Nome
- Rua e Número
- Bairro
- Cep, Cidade e Estado
- Lote
- Endereço de Leitura
- N° do Medidor
- CPF
- Código de Instalação
- Nota Fiscal
- Chave de Acesso Nota Fiscal QRCode
- Protocolo de Autorização Nota Fiscal
- Valores secretos dentro da caixa "Aviso Importante"
- Medidor
- Conta de Energia Elétrica (Número ID)
- Código de Débito Automático do Banco

#### RGE V2

> Values are named in Portuguese, in order.

- Nome
- Rua e Número
- Bairro
- Cep, Cidade e Estado
- Nota Fiscal - Ato Declaratório
- Conta de Energia Elétrica (Número ID) e Série
- Conta Contrato
- Lote
- Roteiro de Leitura
- N° do Medidor
- PN
- Valores secretos dentro da caixa "PREZADO(A) CLIENTE"
- RG
- Código de Instalação
- Código
- Descrição da Operação

- CPF
- Código de Instalação
- Nota Fiscal
- Chave de Acesso Nota Fiscal QRCode
- Protocolo de Autorização Nota Fiscal
- Valores secretos dentro da caixa "Aviso Importante"
- Medidor
- Código de Débito Automático do Banco
