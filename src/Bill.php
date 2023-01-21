<?php

namespace NystronSolar\ElectricBillExtractor;

use Money\Money;
use Smalot\PdfParser\Document;

abstract class Bill
{
    protected Document $document;

    protected ?Money $cost = null;

    protected string $company;

    protected string $country;

    protected string $state;

    public function __construct(Document $_document)
    {
        $this->document = $_document;
    }

    /**
     * Returns the Bill Document
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * Returns the Bill Cost
     * @return ?Money
     */
    public function getCost(): ?Money
    {
        return $this->cost;
    }

    /**
     * Set the Bill Cost
     *
     * @param Document $_document
     * @return self
     */
    public function setCost(Money $_cost): self
    {
        $this->cost = $_cost;

        return $this;
    }

    /**
     * Returns the Bill Company Name
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * Returns the Bill Country
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Returns the Bill State
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }
}