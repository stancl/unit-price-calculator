<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Stancl\UnitPriceCalculator\Parser;
use Stancl\UnitPriceCalculator\Product;
use Stancl\UnitPriceCalculator\Unit;

class ParserTest extends TestCase
{
    protected function normalizeWhitespace(string $string): string
    {
        return str_replace("\u{00A0}", ' ', $string);
    }

    protected function assertSameNormalized(string $expected, string $actual): void
    {
        $this->assertSame($expected, $this->normalizeWhitespace($actual));
    }

    public function testGramsCanBeParsed(): void
    {
        // 1000 for 100g
        // 10000 for 1kg
        $product = Parser::parse('100g', 1000);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(10000, $product->unitPrice);
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$10,000.00 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("10 000,00 Kč / kg", $product->format('cs_CZ', 'CZK'));

        // 123.456 for 17g
        // 7,262.1176470588 for 1kg
        $product = Product::parse('17 g', 123.456);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(7262.12, round($product->unitPrice, 2));
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$7,262.12 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("7 262,12 Kč / kg", $product->format('cs_CZ', 'CZK'));
    }

    public function testKilogramsCanBeParsed(): void
    {
        // 1000 for 100kg
        // 10 for 1kg
        $product = Product::parse('100kg', 1000);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(10, $product->unitPrice);
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$10.00 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("10,00 Kč / kg", $product->format('cs_CZ', 'CZK'));

        // 123.456 for 17kg
        // 7.2621176471 for 1kg
        $product = Product::tryParse('17 kg', 123.456);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(7.26, round($product->unitPrice, 2));
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$7.26 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("7,26 Kč / kg", $product->format('cs_CZ', 'CZK'));
    }

    public function testMillilitersCanBeParsed(): void
    {
        // 1000 for 100ml
        // 10000 for 1L
        $product = Parser::parse('100ml', 1000);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(10000, $product->unitPrice);
        $this->assertSame(Unit::LITER, $product->unit);
        $this->assertSameNormalized('$10,000.00 / L', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("10 000,00 Kč / L", $product->format('cs_CZ', 'CZK'));

        // 123.456 for 17ml
        // 7,262.1176470588 for 1L
        $product = Parser::parse('17 ml', 123.456);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(7262.12, round($product->unitPrice, 2));
        $this->assertSame(Unit::LITER, $product->unit);
        $this->assertSameNormalized('$7,262.12 / L', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("7 262,12 Kč / L", $product->format('cs_CZ', 'CZK'));
    }

    public function testLitersCanBeParsed(): void
    {
        // 1000 for 100L
        // 10 for 1L
        $product = Parser::parse('100l', 1000);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(10, $product->unitPrice);
        $this->assertSame(Unit::LITER, $product->unit);
        $this->assertSameNormalized('$10.00 / L', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("10,00 Kč / L", $product->format('cs_CZ', 'CZK'));

        // 123.456 for 17L
        // 7.2621176471 for 1L
        $product = Parser::parse('17 L', 123.456);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(7.26, round($product->unitPrice, 2));
        $this->assertSame(Unit::LITER, $product->unit);
        $this->assertSameNormalized('$7.26 / L', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("7,26 Kč / L", $product->format('cs_CZ', 'CZK'));
    }

    public function testFormatsCanBeMultipleWords(): void
    {
        // Separate words
        // 100 for 200ml
        // 500 for 1L
        $product = Parser::parse('foo - bar, baz ++ # 123g, 2323ml, 1kg, more words, 200 ml', 100);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(500, $product->unitPrice);
        $this->assertSame(Unit::LITER, $product->unit);
        $this->assertSameNormalized('$500.00 / L', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("500,00 Kč / L", $product->format('cs_CZ', 'CZK'));

        // Single word
        // 222 for 500g
        // 444 for 1kg
        $product = Parser::parse('foo - bar, baz ++ # 123g, 2323ml, 1kg, more words, 500g', 222);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(444, $product->unitPrice);
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$444.00 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("444,00 Kč / kg", $product->format('cs_CZ', 'CZK'));
    }

    public function testParenthesesAreIgnored(): void
    {
        // Separate words
        // 100 for 200g
        // 500 for 1kg
        $product = Parser::parse('foo - bar, baz ++ # (123g), 2323ml, 1kg, more words, (200 g)', 100);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(500, $product->unitPrice);
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$500.00 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("500,00 Kč / kg", $product->format('cs_CZ', 'CZK'));

        // Single word
        // 456 for 500L
        // 2 for 1L
        $product = Parser::parse('foo - bar, baz ++ # 123g, (2323ml), 1kg, more words, (500L)', 1000);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(2, $product->unitPrice);
        $this->assertSame(Unit::LITER, $product->unit);
        $this->assertSameNormalized('$2.00 / L', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("2,00 Kč / L", $product->format('cs_CZ', 'CZK'));
    }

    public function testUnitsInFormatsCanBeFractional(): void
    {
        // Using dot as a decimal separator
        // 1000 for 2,5kg
        // 400 for 1kg
        $product = Parser::parse('2.5kg', 1000);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(400, $product->unitPrice);
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$400.00 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("400,00 Kč / kg", $product->format('cs_CZ', 'CZK'));

        // Using comma as a decimal separator
        // 123.456 for 7,37kg
        // 16.7511533243 for 1kg
        $product = Parser::parse('7,37 kg', 123.456);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(16.75, round($product->unitPrice, 2));
        $this->assertSame(Unit::KILOGRAM, $product->unit);
        $this->assertSameNormalized('$16.75 / kg', $product->format('en_US', 'USD'));
        $this->assertSameNormalized("16,75 Kč / kg", $product->format('cs_CZ', 'CZK'));
    }
}
