<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit;

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV2RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV3RGE;
use NystronSolar\ElectricBillExtractor\ExtractorFactory;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class ExtractorFactoryTest extends TestCase
{
    protected string $v2RGEContent = <<<EOD
    NOME CLIENTE
    R FICTICIA 123 
    BAIRRO
    12345-678 CIDADE RS Nota Fiscal
    Conta de Energia Elétrica
    Nº 024385390 Série U
    Data de Emissão: 28/01/2019
    EOD;

    protected string $v3RGEContent = <<<EOD
    DANF3E - DOCUMENTO AUXILIAR DA  NOTA
    FISCAL DE ENERGIA ELÉTRICA  ELETRÔNICA
    RGE SUL DISTRIBUIDORA DE  ENERGIA S.A.
    EOD;

    protected string $invalidContent = 'A content to be invalid';

    protected function createParserMock(string $text): Parser
    {
        $document = $this->createMock(Document::class);
        $document->expects($this->once())->method('getText')->willReturn($text);

        $parser = $this->createMock(Parser::class);
        $parser->expects($this->once())->method('parseFile')->willReturn($document);

        return $parser;
    }

    public function testIdentifyExtractorClassFromContent(): void
    {
        $this->assertSame(ExtractorV2RGE::class, ExtractorFactory::identifyExtractorClassFromContent($this->v2RGEContent));
        $this->assertSame(ExtractorV3RGE::class, ExtractorFactory::identifyExtractorClassFromContent($this->v3RGEContent));
        $this->assertFalse(ExtractorFactory::identifyExtractorClassFromContent($this->invalidContent));
    }

    public function testIdentifyExtractorClassFromFile(): void
    {
        $this->assertSame(ExtractorV2RGE::class, ExtractorFactory::identifyExtractorClassFromFile('file.pdf', $this->createParserMock($this->v2RGEContent)));
        $this->assertSame(ExtractorV3RGE::class, ExtractorFactory::identifyExtractorClassFromFile('file.pdf', $this->createParserMock($this->v3RGEContent)));
        $this->assertFalse(ExtractorFactory::identifyExtractorClassFromFile('file.pdf', $this->createParserMock($this->invalidContent)));
    }

    public function testInstantiateExtractorFromContent(): void
    {
        $this->assertInstanceOf(ExtractorV2RGE::class, ExtractorFactory::instantiateExtractorFromContent($this->v2RGEContent));
        $this->assertInstanceOf(ExtractorV3RGE::class, ExtractorFactory::instantiateExtractorFromContent($this->v3RGEContent));
        $this->assertFalse(ExtractorFactory::instantiateExtractorFromContent($this->invalidContent));
    }

    public function testInstantiateExtractorFromFile(): void
    {
        $this->assertInstanceOf(ExtractorV2RGE::class, ExtractorFactory::instantiateExtractorFromFile('file.pdf', $this->createParserMock($this->v2RGEContent)));
        $this->assertInstanceOf(ExtractorV3RGE::class, ExtractorFactory::instantiateExtractorFromFile('file.pdf', $this->createParserMock($this->v3RGEContent)));
        $this->assertFalse(ExtractorFactory::instantiateExtractorFromFile('file.pdf', $this->createParserMock($this->invalidContent)));
    }

    public function testExtractFromContent(): void
    {
        $this->assertFalse(ExtractorFactory::extractFromContent($this->invalidContent));
    }

    public function testExtractFromFile(): void
    {
        $this->assertFalse(ExtractorFactory::extractFromFile('file.pdf', $this->createParserMock($this->invalidContent)));
    }

    public function testGetParsedContentFromFile(): void
    {
        $parser = $this->createParserMock($this->v3RGEContent);

        $this->assertSame($this->v3RGEContent, $parser->parseFile('file.pdf')->getText());
    }
}
