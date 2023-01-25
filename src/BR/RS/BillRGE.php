<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

class BillRGE extends BillRS
{
    protected string $company = 'RGE';

    protected ?ClientRGE $client = null;

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
}