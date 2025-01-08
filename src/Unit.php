<?php

namespace Stancl\UnitPriceCalculator;

class Unit
{
    public const GRAM = 'g';
    public const KILOGRAM = 'kg';

    public const LITER = 'l';
    public const MILLILITER = 'ml';

    public const UNITS = [
        self::GRAM,
        self::KILOGRAM,
        self::LITER,
        self::MILLILITER,
    ];

    /**
     * Returns the base unit and the multiple to convert the given unit to the base unit.
     *
     * @param string $unit
     * @return array{string, int}
     */
    public static function toBaseUnit(string $unit): array
    {
        return [
            self::KILOGRAM => [self::KILOGRAM, 1],
            self::GRAM => [self::KILOGRAM, 1000],
            self::LITER => [self::LITER, 1],
            self::MILLILITER => [self::LITER, 1000],
        ][$unit];
    }
}
