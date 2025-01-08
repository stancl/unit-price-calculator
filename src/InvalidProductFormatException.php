<?php

namespace Stancl\UnitPriceCalculator;

class InvalidProductFormatException extends \Exception
{
    public function __construct(string $format)
    {
        parent::__construct("Invalid product format: $format");
    }
}
