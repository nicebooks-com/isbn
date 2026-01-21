<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\Exception\InvalidIsbnException;
use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Exception\IsbnNotConvertibleException;
use Nicebooks\Isbn\Isbn;
use Nicebooks\Isbn\Isbn10;
use Nicebooks\Isbn\Isbn13;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for class Isbn.
 */
class IsbnTest extends TestCase
{
    /**
     * @param Isbn   $isbn   The Isbn instance to test.
     * @param string $string The expected string value of the ISBN.
     * @param bool   $is13   Whether the ISBN is expected to be an ISBN-13.
     */
    private static function assertIsbnEquals(Isbn $isbn, string $string, bool $is13): void
    {
        self::assertSame($string, (string) $isbn);
        self::assertSame($string, $isbn->toString());
        self::assertSame($is13, $isbn->is13());
        self::assertSame(!$is13, $isbn->is10());
    }

    /**
     * @param string $isbn   The input ISBN.
     * @param string $string The expected string value of the resulting Isbn object.
     * @param bool   $is13   Whether the ISBN is expected to be an ISBN-13.
     */
    #[DataProvider('providerOf')]
    public function testOf(string $isbn, string $string, bool $is13): void
    {
        self::assertIsbnEquals(Isbn::of($isbn), $string, $is13);
    }

    public static function providerOf(): array
    {
        return [
            [' 1-234-56789-x ',     '123456789X',    false],
            [' 978-0-00-000000-2 ', '9780000000002', true],
        ];
    }

    #[DataProvider('providerOfIsbn10')]
    public function testOfIsbn10(string $isbn, string $expected): void
    {
        self::assertIsbnEquals(Isbn10::of($isbn), $expected, false);
    }

    public static function providerOfIsbn10(): array
    {
        return [
            [' 1-234-56789-x ',  '123456789X'],
            [' 978-1234567897 ', '123456789X'],
        ];
    }

    #[DataProvider('providerOfIsbn10ThrowsException')]
    public function testOfIsbn10ThrowsException(string $isbn): void
    {
        $this->expectException(IsbnNotConvertibleException::class);
        Isbn10::of($isbn);
    }

    public static function providerOfIsbn10ThrowsException(): array
    {
        return [
            [' 979-1234567896 '],
        ];
    }

    #[DataProvider('providerOfIsbn13')]
    public function testOfIsbn13(string $isbn, string $expected): void
    {
        self::assertIsbnEquals(Isbn13::of($isbn), $expected, true);
    }

    public static function providerOfIsbn13(): array
    {
        return [
            [' 1-234-56789-x ',  '9781234567897'],
            [' 978-1234567897 ', '9781234567897'],
            [' 979-1234567896 ', '9791234567896'],
        ];
    }

    /**
     * @param string $invalidIsbn The invalid ISBN.
     */
    #[DataProvider('providerOfInvalidIsbnThrowsException')]
    public function testOfInvalidIsbnThrowsException(string $invalidIsbn): void
    {
        $this->expectException(InvalidIsbnException::class);
        Isbn::of($invalidIsbn);
    }

    public static function providerOfInvalidIsbnThrowsException(): array
    {
        return [
            ['123456789'],
            ['1234567890'],
            ['12345678901'],
            ['123456789012'],
            ['1234567890123'],
            ['9780000000000'],
            ['9790000000000'],
            ["123456789X\x80"],
        ];
    }

    /**
     * @param string $isbn13 The input ISBN-13.
     * @param string $isbn10 The expected ISBN-10 output.
     */
    #[DataProvider('providerTo10')]
    public function testTo10(string $isbn13, string $isbn10): void
    {
        $inputIsbn = Isbn::of($isbn13);
        $outputIsbn = $inputIsbn->to10();

        // Test the input object as well to ensure it's unaffected.
        self::assertIsbnEquals($inputIsbn, $isbn13, true);
        self::assertIsbnEquals($outputIsbn, $isbn10, false);
    }

    public static function providerTo10(): array
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

    public function test10to10ReturnsThis(): void
    {
        $isbn = Isbn::of('123456789X');
        self::assertSame($isbn, $isbn->to10());
    }

    /**
     * @param string $isbn          The ISBN to test.
     * @param bool   $isConvertible Whether the ISBN is convertible to an ISBN-10.
     */
    #[DataProvider('providerIsConvertibleTo10')]
    public function testIsConvertibleTo10(string $isbn, bool $isConvertible): void
    {
        self::assertSame($isConvertible, Isbn::of($isbn)->isConvertibleTo10());
    }

    public static function providerIsConvertibleTo10(): array
    {
        return [
            ['0123456789',    true],
            ['123456789X',    true],
            ['9780123456786', true],
            ['9781234567897', true],
            ['9790000000001', false],
            ['9791234567896', false],
        ];
    }

    /**
     * @param string $isbn13 The non-convertible ISBN-13.
     */
    #[DataProvider('providerNotConvertibleTo10')]
    public function testNotConvertibleTo10ThrowsException(string $isbn13): void
    {
        $this->expectException(IsbnNotConvertibleException::class);
        Isbn::of($isbn13)->to10();
    }

    public static function providerNotConvertibleTo10(): array
    {
        return [
            ['9790000000001'],
            ['9791234567896'],
        ];
    }

    /**
     * @param string $isbn10 The input ISBN-10.
     * @param string $isbn13 The expected ISBN-13 output.
     */
    #[DataProvider('providerTo13')]
    public function testTo13(string $isbn10, string $isbn13): void
    {
        $inputIsbn = Isbn::of($isbn10);
        $outputIsbn = $inputIsbn->to13();

        // Test the input object as well to ensure it's unaffected.
        self::assertIsbnEquals($inputIsbn, $isbn10, false);
        self::assertIsbnEquals($outputIsbn, $isbn13, true);
    }

    public static function providerTo13(): array
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

    public function test13to13ReturnsThis(): void
    {
        $isbn = Isbn::of('9784567890120');
        self::assertSame($isbn, $isbn->to13());
    }

    /**
     * @param string $isbn           The ISBN to test.
     * @param string $expectedFormat The expected formatted output.
     * @param string $expectedGroup  The expected group name.
     */
    #[DataProvider('providerInfoAndFormat')]
    public function testInfoAndFormat(string $isbn, string $expectedFormat, string $expectedGroup): void
    {
        $isbn = Isbn::of($isbn);
        $expectedParts = explode('-', $expectedFormat);

        self::assertSame($expectedFormat, $isbn->format());
        self::assertSame($expectedFormat, $isbn->toFormattedString());
        self::assertSame($expectedParts, $isbn->getParts());

        if ($isbn->is13()) {
            $groupIdentifier = $expectedParts[0] . '-' . $expectedParts[1];
            $publisherIdentifier = $expectedParts[2];
            $titleIdentifier = $expectedParts[3];
            $checkDigit = $expectedParts[4];
        } else {
            $groupIdentifier = $expectedParts[0];
            $publisherIdentifier = $expectedParts[1];
            $titleIdentifier = $expectedParts[2];
            $checkDigit = $expectedParts[3];
        }

        self::assertSame($groupIdentifier, $isbn->getGroupIdentifier());
        self::assertSame($publisherIdentifier, $isbn->getPublisherIdentifier());
        self::assertSame($titleIdentifier, $isbn->getTitleIdentifier());
        self::assertSame($checkDigit, $isbn->getCheckDigit());

        self::assertSame($expectedGroup, $isbn->getGroupName());
    }

    public static function providerInfoAndFormat(): array
    {
        return [
            ['0001234560',    '0-00-123456-0',     'English language'],
            ['0321234561',    '0-321-23456-1',     'English language'],
            ['0765432102',    '0-7654-3210-2',     'English language'],
            ['0876543212',    '0-87654-321-2',     'English language'],
            ['0912345675',    '0-912345-67-5',     'English language'],
            ['0987654322',    '0-9876543-2-2',     'English language'],
            ['9995501236',    '99955-0-123-6',     'Srpska, Republic of'],
            ['9995523450',    '99955-23-45-0',     'Srpska, Republic of'],
            ['999556789X',    '99955-678-9-X',     'Srpska, Republic of'],

            ['9780001234567', '978-0-00-123456-7', 'English language'],
            ['9780321234568', '978-0-321-23456-8', 'English language'],
            ['9780765432100', '978-0-7654-3210-0', 'English language'],
            ['9780876543214', '978-0-87654-321-4', 'English language'],
            ['9780912345673', '978-0-912345-67-3', 'English language'],
            ['9780987654328', '978-0-9876543-2-8', 'English language'],
            ['9789995501235', '978-99955-0-123-5', 'Srpska, Republic of'],
            ['9789995523459', '978-99955-23-45-9', 'Srpska, Republic of'],
            ['9789995567897', '978-99955-678-9-7', 'Srpska, Republic of'],
            ['9791001234563', '979-10-01-23456-3', 'France'],
            ['9791023456783', '979-10-234-5678-3', 'France'],
            ['9791078901238', '979-10-7890-123-8', 'France'],
            ['9791090123458', '979-10-90123-45-8', 'France'],
            ['9791098765438', '979-10-987654-3-8', 'France'],
        ];
    }

    #[DataProvider('providerIsValidGroupAndRange')]
    public function testIsValidGroupAndRange(string $isbnString, bool $hasValidRegistrationGroup, bool $isValid): void
    {
        $isbn = Isbn::of($isbnString);

        self::assertSame($hasValidRegistrationGroup, $isbn->isValidGroup());
        self::assertSame($hasValidRegistrationGroup, $isbn->hasValidRegistrationGroup());
        self::assertSame($isValid, $isbn->isValidRange());
        self::assertSame($isValid, $isbn->isValid());

        if (!$hasValidRegistrationGroup) {
            self::assertException(IsbnException::class, static function () use ($isbn) {
                $isbn->getRegistrationGroup();
            });
            self::assertException(IsbnException::class, static function () use ($isbn) {
                $isbn->getGroupIdentifier();
            });
            self::assertException(IsbnException::class, static function () use ($isbn) {
                $isbn->getGroupName();
            });

            // ISBN with invalid group/range cannot be formatted
            self::assertSame($isbnString, $isbn->format());
        }

        if (!$isValid) {
            self::assertException(IsbnException::class, static function () use ($isbn) {
                $isbn->getPublisherIdentifier();
            });
            self::assertException(IsbnException::class, static function () use ($isbn) {
                $isbn->getTitleIdentifier();
            });
            self::assertException(IsbnException::class, static function () use ($isbn) {
                $isbn->getParts();
            });
        }
    }

    public static function providerIsValidGroupAndRange(): array
    {
        return [
            ['0001234560',    true,  true],
            ['9983000008',    true,  false],
            ['9999999999',    false, false],
            ['9780001234567', true,  true],
            ['9789983000009', true,  false],
            ['9789999999991', false, false],
        ];
    }

    /**
     * Asserts that an exception is thrown when calling the given function.
     *
     * @throws \Exception
     */
    private function assertException(string $expectedException, callable $function): void
    {
        $this->addToAssertionCount(1);

        try {
            $function();
        } catch (\Exception $e) {
            if ($e instanceof $expectedException) {
                return;
            }

            throw $e;
        }

        self::fail('Failed asserting that exception of type ' . $expectedException . ' is thrown.');
    }

    /**
     * @param string $isbn        Any ISBN.
     * @param string $anotherIsbn The ISBN-10 that is expected to be equal to $isbn.
     */
    #[DataProvider('providerIsEqualTo')]
    public function testIsEqualTo(string $isbn, string $anotherIsbn, bool $isEqual): void
    {
        self::assertSame($isEqual, Isbn::of($isbn)->isEqualTo(Isbn::of($anotherIsbn)));
    }

    public static function providerIsEqualTo(): array
    {
        return [
            ['9780123456786', '0123456789',    true],
            ['9781234567897', '123456789X',    true],
            ['5678901230',    '9785678901231', true],
            ['6789012346',    '9786789012342', true],
            ['9783456789019', '123456789X',    false],
            ['9784567890120', '123456789X',    false],
            ['8901234564',    '9786789012342', false],
            ['9012345677',    '9786789012342', false],
        ];
    }
}
