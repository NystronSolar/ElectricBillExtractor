<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

class BillRGE extends BillRS
{
    protected string $company = 'RGE';

    protected ?ClientRGE $client = null;

    protected ?int $batch = null;

    protected ?string $readingGuide = null;

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
}