<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\Exception\InvalidIsbnException;
use Nicebooks\Isbn\Exception\IsbnNotConvertibleException;
use Nicebooks\Isbn\IsbnTools;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for class IsbnTools conversion.
 */
class IsbnConverterTest extends TestCase
{
    /**
     * @param string $isbn10 The input ISBN-10.
     * @param string $isbn13 The expected output ISBN-13.
     */
    #[DataProvider('providerConvertIsbn10to13')]
    public function testConvertIsbn10to13(string $isbn10, string $isbn13): void
    {
        $tools = new IsbnTools();
        self::assertSame($isbn13, $tools->convertIsbn10to13($isbn10));
    }

    public static function providerConvertIsbn10to13(): array
    {
        return [
            ['0123456789', '9780123456786'],
            ['123456789X', '9781234567897'],
            ['2345678909', '9782345678908'],
            ['3456789017', '9783456789019'],
            ['4567890124', '9784567890120'],
            ['5678901230', '9785678901231'],
            ['6789012346', '9786789012342'],
            ['7890123450', '9787890123453'],
            ['8901234564', '9788901234564'],
            ['9012345677', '9789012345675'],
        ];
    }

    #[DataProvider('providerConvertInvalidIsbn10to13')]
    public function testConvertInvalidIsbn10to13(string $isbn): void
    {
        $tools = new IsbnTools();

        $this->expectException(InvalidIsbnException::class);
        $tools->convertIsbn10to13($isbn);
    }

    public static function providerConvertInvalidIsbn10to13(): array
    {
        return [
            ["0123456789\x80"],
            ['X123456789'],
            ['0123456788'],
        ];
    }

    /**
     * @param string $isbn13 The input ISBN-13.
     * @param string $isbn10 The expected output ISBN-10.
     */
    #[DataProvider('providerConvertIsbn13to10')]
    public function testConvertIsbn13to10(string $isbn13, string $isbn10): void
    {
        $tools = new IsbnTools();
        self::assertSame($isbn10, $tools->convertIsbn13to10($isbn13));
    }

    public static function providerConvertIsbn13to10(): array
    {
        return [
            ['9780123456786', '0123456789'],
            ['9781234567897', '123456789X'],
            ['9782345678908', '2345678909'],
            ['9783456789019', '3456789017'],
            ['9784567890120', '4567890124'],
            ['9785678901231', '5678901230'],
            ['9786789012342', '6789012346'],
            ['9787890123453', '7890123450'],
            ['9788901234564', '8901234564'],
            ['9789012345675', '9012345677'],
        ];
    }

    #[DataProvider('providerConvertInvalidIsbn13to10')]
    public function testConvertInvalidIsbn13to10(string $isbn): void
    {
        $tools = new IsbnTools();

        $this->expectexception(InvalidIsbnException::class);
        $tools->convertIsbn13to10($isbn);
    }

    public static function providerConvertInvalidIsbn13to10()
    {
        return [
            ["9780123456786\x80"],
            ['9770123456786'],
            ['9780123456789'],
        ];
    }

    /**
     * @param string $isbn An ISBN-13 not convertible to ISBN-10.
     */
    #[DataProvider('providerIsbnNotConvertibleThrowsException')]
    public function testIsbnNotConvertibleThrowsException(string $isbn): void
    {
        $tools = new IsbnTools();

        $this->expectException(IsbnNotConvertibleException::class);
        $tools->convertIsbn13to10($isbn);
    }

    public static function providerIsbnNotConvertibleThrowsException(): array
    {
        return [
            ['9790123456785'],
            ['9799012345674'],
        ];
    }
}
