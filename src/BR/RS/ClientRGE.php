<?php

namespace NystronSolar\ElectricBillExtractor\BR\RS;

class ClientRGE
{
    protected string $name;

    protected string $address;

    protected string $district;

    protected string $city;

    public function __construct(string $_name, string $_address, string $_district, string $_city)
    {
        $this->name = $_name;
        $this->address = $_address;
        $this->district = $_district;
        $this->city = $_city;
    }

    /**
     * Returns the Client Name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * Returns the Client Address.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }
    /**
     * Returns the Client District.
     *
     * @return string
     */
    public function getDistrict(): string
    {
        return $this->district;
    }
    /**
     * Returns the Client City.
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }


}