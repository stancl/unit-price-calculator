<?php

namespace Stancl\UnitPriceCalculator;

/**
 * Supported formats (case-insensitive):
 * 100g, 100 g
 * 100ml, 100 ml
 * 10kg, 10 kg
 * 20L, 20 l
 */
class Parser
{
    /**
     * @throws InvalidProductFormatException
     * @param int|float $price
     */
    public static function parse(string $format, $price): Product
    {
        [$unit, $unitAmount] = static::getUnitAndUnitAmount($format);
        [$baseUnit, $multiple] = Unit::toBaseUnit($unit);

        return new Product($baseUnit, $price / $unitAmount * $multiple);
    }

    /**
     * @throws InvalidProductFormatException
     * @return array{string, int}
     */
    public static function getUnitAndUnitAmount(string $format): array
    {
        $format = strtolower($format);
        $words = explode(' ', $format);

        $words = array_map(function ($word) {
            $trimmed = rtrim(ltrim($word, '('), ')');

            // Replace commas with dots for decimal parsing
            return str_replace(',', '.', $trimmed);
        }, $words);

        $count = count($words);

        $unit = null;
        $unitAmount = null;

        for ($i = $count - 1; $i >= 0; $i--) {
            $word = $words[$i];

            if ($unit === null) {
                if (in_array($word, Unit::UNITS)) {
                    $unit = $word;
                }

                $unitAndAmount = static::parseUnitAndAmount($word);
                if ($unitAndAmount !== null) {
                    $unit = $unitAndAmount[0];
                    $unitAmount = $unitAndAmount[1];
                    break;
                }

                continue;
            }

            if (is_numeric($word)) {
                $unitAmount = $word + 0;
                break;
            }
        }

        if ($unit === null || $unitAmount === null) {
            throw new InvalidProductFormatException($format);
        }

        return [$unit, $unitAmount];
    }

    protected static function parseUnitAndAmount(string $word): ?array
    {
        foreach (Unit::UNITS as $unit) {
            if (substr($word, -strlen($unit)) === $unit) {
                $amount = substr($word, 0, -strlen($unit));
                if (is_numeric($amount)) {
                    return [$unit, $amount + 0];
                }
            }
        }

        return null;
    }
}
