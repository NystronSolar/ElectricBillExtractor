<?php

namespace NystronSolar\ElectricBillExtractor;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

abstract class Extractor
{
    protected Parser $parser;

    protected ?Bill $bill;

    protected ?Document $document;

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
     * Returns the Bill
     * @return ?Bill
     */
    public function getBill(): ?Bill
    {
        return $this->bill;
    }

    /**
     * Set the Bill
     *
     * @param Bill $_bill
     * @return self
     */
    public function setBill(Bill $_bill): self
    {
        $this->bill = $_bill;

        return $this;
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
     * Extract the Bill from an File.
     * Return False in case of error.
     * @param string $filename
     * @return Bill|false
     */
    public function fromFile(string $filename): Bill|false
    {
        $this->setDocument($this->parser->parseFile($filename));

        $bill = $this->extract($this->document->getText());

        return $bill;
    }

    /**
     * Extract the Bill from an Content (String).
     * Return False in case of error.
     * @param string $content
     * @return Bill|false
     */
    public function fromContent(string $content): Bill|false
    {
        $this->setDocument($this->parser->parseContent($content));

        $bill = $this->extract($this->document->getText());

        return $bill;
    }

    abstract protected function extract(string $content): Bill|false;
}