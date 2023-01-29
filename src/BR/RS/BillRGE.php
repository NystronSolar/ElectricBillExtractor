<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

use DateTimeImmutable;

class BillRGE extends BillRS
{
    protected string $company = 'RGE';

    protected ?ClientRGE $client = null;

    protected ?int $batch = null;

    protected ?string $readingGuide = null;

    protected ?int $powerMeterId = null;

    protected ?array $pages = null;

    protected ?DateTimeImmutable $deliveryDate = null;

    protected ?DateTimeImmutable $nextReadingDate = null;

    /**
     * Returns the Bill Client
     * @return ?ClientRGE
     */
    public function getClient(): ?ClientRGE
    {
        return $this->client;
    }

    /**
     * Set the Bill Client
     *
     * @param ClientRGE $_client
     * @return self
     */
    public function setClient(ClientRGE $_client): self
    {
        $this->client = $_client;

        return $this;
    }

    /**
     * Returns the Bill Batch
     * @return ?int
     */
    public function getBatch(): ?int
    {
        return $this->batch;
    }

    /**
     * Set the Bill Batch
     *
     * @param int $_batch
     * @return self
     */
    public function setBatch(int $_batch): self
    {
        $this->batch = $_batch;

        return $this;
    }

    /**
     * Returns the Bill Reading Guide
     * @return ?string
     */
    public function getReadingGuide(): ?string
    {
        return $this->readingGuide;
    }

    /**
     * Set the Bill Reading Guide
     *
     * @param string $_readingGuide
     * @return self
     */
    public function setReadingGuide(string $_readingGuide): self
    {
        $this->readingGuide = $_readingGuide;

        return $this;
    }

    /**
     * Returns the Bill Power Meter Id
     * @return ?int
     */
    public function getPowerMeterId(): ?int
    {
        return $this->powerMeterId;
    }

    /**
     * Set the Bill Power Meter Id
     *
     * @param int $_powerMeterId
     * @return self
     */
    public function setPowerMeterId(int $_powerMeterId): self
    {
        $this->powerMeterId = $_powerMeterId;

        return $this;
    }

    /**
     * Returns the Bill Pages
     * @return ?array
     */
    public function getPages(): ?array
    {
        return $this->pages;
    }

    /**
     * Set the Bill Pages
     *
     * @param array $_pages
     * @return self
     */
    public function setPages(array $_pages): self
    {
        $this->pages = $_pages;

        return $this;
    }

    /**
     * Returns the Bill Delivery Date
     * @return ?DateTimeImmutable
     */
    public function getDeliveryDate(): ?DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    /**
     * Set the Bill Delivery Date
     *
     * @param DateTimeImmutable $_deliveryDate
     * @return self
     */
    public function setDeliveryDate(DateTimeImmutable $_deliveryDate): self
    {
        $this->deliveryDate = $_deliveryDate;

        return $this;
    }

    /**
     * Returns the Bill Next Reading Date
     * @return ?DateTimeImmutable
     */
    public function getNextReadingDate(): ?DateTimeImmutable
    {
        return $this->nextReadingDate;
    }

    /**
     * Set the Bill Next Reading Date
     *
     * @param DateTimeImmutable $nextReadingDate
     * @return self
     */
    public function setNextReadingDate(DateTimeImmutable $nextReadingDate): self
    {
        $this->nextReadingDate = $nextReadingDate;

        return $this;
    }
}