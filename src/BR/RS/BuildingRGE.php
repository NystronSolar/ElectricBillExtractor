<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

class BuildingRGE
{
    protected string $classification;

    protected string $supplyType;

    protected int $voltage;

    protected string $installationCode;

    public function __construct(string $_classification, string $_supplyType, int $_voltage)
    {
        $this->classification = $_classification;
        $this->supplyType = $_supplyType;
        $this->voltage = $_voltage;
    }

    /**
     * Returns the Building Classification.
     *
     * @return string
     */

    public function getClassification(): string
    {
        return $this->classification;
    }
    /**
     * Returns the Building Supply Type.
     *
     * @return string
     */

    public function getSupplyType(): string
    {
        return $this->supplyType;
    }

    /**
     * Returns the Building Voltage.
     *
     * @return int
     */

    public function getVoltage(): int
    {
        return $this->voltage;
    }

    /**
     * Returns the Building Installation Code.
     *
     * @return string
     */

    public function getInstallationCode(): string
    {
        return $this->installationCode;
    }

    /**
     * Set the Building Installation Code.
     *
     * @param string $_installationCode
     * @return self
     */

    public function setInstallationCode(string $_installationCode): self
    {
        $this->installationCode = $_installationCode;

        return $this;
    }
}