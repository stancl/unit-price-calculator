<?php

namespace Stancl\UnitPriceCalculator;

use NumberFormatter;

class Product
{
    /** @see Unit */
    public $unit;

    /** @var int|float */
    public $unitPrice;

    public function __construct($unit, $unitPrice)
    {
        $this->unit = $unit;
        $this->unitPrice = $unitPrice;
    }

    /** @throws InvalidProductFormatException */
    public static function from(string $format, $price): Product
    {
        return Parser::parse($format, $price);
    }

    public static function tryFrom(string $format, $price): ?Product
    {
        try {
            return Parser::parse($format, $price);
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function format(string $locale, string $currency): string
    {
        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formattedPrice = $numberFormatter->formatCurrency($this->unitPrice, $currency);

        // We capitalize liters for easy readability. This is only done here to
        // simplify the parsing process by keeping everything uniform as lowercase.
        $unit = $this->unit === Unit::LITER ? 'L' : $this->unit;

        return "$formattedPrice / " . $unit;
    }
}
