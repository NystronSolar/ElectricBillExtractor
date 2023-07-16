<?php

use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV1RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV2RGE;
use NystronSolar\ElectricBillExtractor\Extractor\ExtractorV3RGE;
use NystronSolar\ElectricBillExtractor\ExtractorFactory;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class ExtractorFactoryTest extends TestCase
{
    protected string $v1RGEContent = <<<EOD
    NOME CLIENTE
    R FICTICIA 123 
    BAIRRO
    12345-678 CIDADE RS Nota Fiscal 
    EOD;

    protected string $v2RGEContent = <<<EOD
    NOME CLIENTE
    R FICTICIA 123 
    BAIRRO
    12345-678 CIDADE RS Nota Fiscal  / RE - Ato Declaratório nº  2023/001
    EOD;

    protected string $v3RGEContent = <<<EOD
    DANF3E - DOCUMENTO AUXILIAR DA  NOTA
    FISCAL DE ENERGIA ELÉTRICA  ELETRÔNICA
    RGE SUL DISTRIBUIDORA DE  ENERGIA S.A.
    EOD;

    // V1 RGE Bills text starts with the client name.
    public function testIdentifyExtractorClassV1RGE(): void
    {
        $content = $this->v1RGEContent;

        $this->assertSame(ExtractorV1RGE::class, ExtractorFactory::identifyExtractorClassFromContent($content));
    }

    public function testIdentifyExtractorClassV2RGE(): void
    {
        $content = $this->v2RGEContent;

        $this->assertSame(ExtractorV2RGE::class, ExtractorFactory::identifyExtractorClassFromContent($content));
    }

    public function testIdentifyExtractorClassV3RGE(): void
    {
        $content = $this->v3RGEContent;

        $this->assertSame(ExtractorV3RGE::class, ExtractorFactory::identifyExtractorClassFromContent($content));
    }

    public function testIdentifyExtractorClassFromFile(): void
    {
        $document = $this->createMock(Document::class);
        $document->expects($this->once())->method('getText')->willReturn($this->v1RGEContent);

        $parser = $this->createMock(Parser::class);
        $parser->expects($this->once())->method('parseFile')->willReturn($document);

        $this->assertSame(ExtractorV1RGE::class, ExtractorFactory::identifyExtractorClassFromFile('file.pdf', $parser));
    }

    public function testInstantiateExtractorFromContent(): void
    {
        $content = $this->v1RGEContent;

        $this->assertInstanceOf(ExtractorV1RGE::class, ExtractorFactory::instantiateExtractorFromContent($content));
    }

    public function testInstantiateExtractorFromFile(): void
    {
        $document = $this->createMock(Document::class);
        $document->expects($this->once())->method('getText')->willReturn($this->v1RGEContent);

        $parser = $this->createMock(Parser::class);
        $parser->expects($this->once())->method('parseFile')->willReturn($document);

        $this->assertInstanceOf(ExtractorV1RGE::class, ExtractorFactory::instantiateExtractorFromFile('file.pdf', $parser));
    }
}
