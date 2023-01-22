<?php

namespace NystronSolar\ElectricBillExtractor\BR;

use NystronSolar\ElectricBillExtractor\Bill;

abstract class BillBR extends Bill
{
    protected string $country = 'BR';
}