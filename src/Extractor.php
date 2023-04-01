<?php

namespace NystronSolar\ElectricBillExtractor;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

abstract class Extractor
{
    protected Parser $parser;

    protected ?Document $document;

    protected ?array $bill;

    protected array $contentExploded;

    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?? new Parser();
    }

    /**
     * Returns the Bill Document Parser
     * @return Parser
     */
    public function getParser(): Parser
    {
        return $this->parser;
    }

    /**
     * Returns the Bill Document
     * @return ?Document
     */
    public function getDocument(): ?Document
    {
        return $this->document;
    }

    /**
     * Set the Bill Document
     *
     * @param Document $_document
     * @return self
     */
    private function setDocument(Document $_document): self
    {
        $this->document = $_document;
        $this->contentExploded = explode(PHP_EOL, $this->document->getText());

        return $this;
    }

    /**
     * Returns the Bill
     * @return ?array
     */
    public function getBill(): ?array
    {
        return $this->bill;
    }

    /**
     * Set the Bill
     *
     * @param array $_bill
     * @return self
     */
    public function setBill(array $_bill): self
    {
        $this->bill = $_bill;

        return $this;
    }

    /**
     * Extract the Bill from an File.
     * Return False in case of error.
     * @param string $filename
     * @return array|false
     */
    public function fromFile(string $filename): array|false
    {
        $this->setDocument($this->parser->parseFile($filename));

        $bill = $this->extract();

        return $bill;
    }

    /**
     * Extract the Bill from an Content (String).
     * Return False in case of error.
     * @param string $content
     * @return array|false
     */
    public function fromContent(string $content): array|false
    {
        $this->setDocument($this->parser->parseContent($content));

        $bill = $this->extract();

        return $bill;
    }

    /**
     * Extract the Bill from an PDF Document.
     * Return False in case of error.
     * @param Document $document
     * @return array|false
     */
    public function fromDocument(Document $document): array|false
    {
        $this->setDocument($document);

        $bill = $this->extract();

        return $bill;
    }

    abstract protected function extract(): array|false;
}