<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Isbn;
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
    private function assertIsbnEquals(Isbn $isbn, string $string, bool $is13) : void
    {
        $this->assertSame($string, (string) $isbn);
        $this->assertSame($is13, $isbn->is13());
        $this->assertSame(! $is13, $isbn->is10());
    }

    /**
     * @dataProvider providerGet
     *
     * @param string $isbn   The input ISBN.
     * @param string $string The expected string value of the resulting Isbn object.
     * @param bool   $is13   Whether the ISBN is expected to be an ISBN-13.
     */
    public function testGet(string $isbn, string $string, bool $is13) : void
    {
        $this->assertIsbnEquals(Isbn::of($isbn), $string, $is13);
    }

    /**
     * @return array
     */
    public function providerGet() : array
    {
        return [
            [' 1-234-56789-x ', '123456789X', false],
            [' 978-0-00-000000-2 ', '9780000000002', true],
        ];
    }

    /**
     * @dataProvider providerGetInvalidIsbnThrowsException
     * @expectedException \Nicebooks\Isbn\Exception\InvalidIsbnException
     *
     * @param string $invalidIsbn The invalid ISBN.
     */
    public function testGetInvalidIsbnThrowsException(string $invalidIsbn) : void
    {
        Isbn::of($invalidIsbn);
    }

    /**
     * @return array
     */
    public function providerGetInvalidIsbnThrowsException() : array
    {
        return [
            ['123456789'],
            ['1234567890'],
            ['12345678901'],
            ['123456789012'],
            ['1234567890123'],
            ['9780000000000'],
            ['9790000000000'],
            ["123456789X\x80"]
        ];
    }

    /**
     * @dataProvider providerTo10
     *
     * @param string $isbn13 The input ISBN-13.
     * @param string $isbn10 The expected ISBN-10 output.
     */
    public function testTo10(string $isbn13, string $isbn10) : void
    {
        $inputIsbn = Isbn::of($isbn13);
        $outputIsbn = $inputIsbn->to10();

        // Test the input object as well to ensure it's unaffected.
        $this->assertIsbnEquals($inputIsbn, $isbn13, true);
        $this->assertIsbnEquals($outputIsbn, $isbn10, false);
    }

    /**
     * @return array
     */
    public function providerTo10() : array
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

    public function test10to10ReturnsThis() : void
    {
        $isbn = Isbn::of('123456789X');
        $this->assertSame($isbn, $isbn->to10());
    }

    /**
     * @dataProvider providerIsConvertibleTo10
     *
     * @param string $isbn          The ISBN to test.
     * @param bool   $isConvertible Whether the ISBN is convertible to an ISBN-10.
     */
    public function testIsConvertibleTo10(string $isbn, bool $isConvertible) : void
    {
        $this->assertSame($isConvertible, Isbn::of($isbn)->isConvertibleTo10());
    }

    /**
     * @return array
     */
    public function providerIsConvertibleTo10() : array
    {
        return [
            ['0123456789', true],
            ['123456789X', true],
            ['9780123456786', true],
            ['9781234567897', true],
            ['9790000000001', false],
            ['9791234567896', false],
        ];
    }

    /**
     * @dataProvider providerNotConvertibleTo10
     * @expectedException \Nicebooks\Isbn\Exception\IsbnNotConvertibleException
     *
     * @param string $isbn13 The non-convertible ISBN-13.
     */
    public function testNotConvertibleTo10ThrowsException(string $isbn13) : void
    {
        Isbn::of($isbn13)->to10();
    }

    /**
     * @return array
     */
    public function providerNotConvertibleTo10() : array
    {
        return [
            ['9790000000001'],
            ['9791234567896'],
        ];
    }

    /**
     * @dataProvider providerTo13
     *
     * @param string $isbn10 The input ISBN-10.
     * @param string $isbn13 The expected ISBN-13 output.
     */
    public function testTo13(string $isbn10, string $isbn13) : void
    {
        $inputIsbn = Isbn::of($isbn10);
        $outputIsbn = $inputIsbn->to13();

        // Test the input object as well to ensure it's unaffected.
        $this->assertIsbnEquals($inputIsbn, $isbn10, false);
        $this->assertIsbnEquals($outputIsbn, $isbn13, true);
    }

    /**
     * @return array
     */
    public function providerTo13() : array
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

    public function test13to13ReturnsThis() : void
    {
        $isbn = Isbn::of('9784567890120');
        $this->assertSame($isbn, $isbn->to13());
    }

    /**
     * @dataProvider providerInfoAndFormat
     *
     * @param string $isbn           The ISBN to test.
     * @param string $expectedFormat The expected formatted output.
     * @param string $expectedGroup  The expected group name.
     */
    public function testInfoAndFormat(string $isbn, string $expectedFormat, string $expectedGroup) : void
    {
        $isbn = Isbn::of($isbn);
        $expectedParts = explode('-', $expectedFormat);

        $this->assertSame($expectedFormat, $isbn->format());
        $this->assertSame($expectedParts, $isbn->getParts());

        if ($isbn->is13()) {
            $groupIdentifier     = $expectedParts[0] . '-' . $expectedParts[1];
            $publisherIdentifier = $expectedParts[2];
            $titleIdentifier     = $expectedParts[3];
            $checkDigit          = $expectedParts[4];
        } else {
            $groupIdentifier     = $expectedParts[0];
            $publisherIdentifier = $expectedParts[1];
            $titleIdentifier     = $expectedParts[2];
            $checkDigit          = $expectedParts[3];
        }

        $this->assertSame($groupIdentifier, $isbn->getGroupIdentifier());
        $this->assertSame($publisherIdentifier, $isbn->getPublisherIdentifier());
        $this->assertSame($titleIdentifier, $isbn->getTitleIdentifier());
        $this->assertSame($checkDigit, $isbn->getCheckDigit());

        $this->assertSame($expectedGroup, $isbn->getGroupName());
    }

    /**
     * @return array
     */
    public function providerInfoAndFormat() : array
    {
        return [
            ['0001234560', '0-00-123456-0', 'English language'],
            ['0321234561', '0-321-23456-1', 'English language'],
            ['0765432102', '0-7654-3210-2', 'English language'],
            ['0876543212', '0-87654-321-2', 'English language'],
            ['0912345675', '0-912345-67-5', 'English language'],
            ['0987654322', '0-9876543-2-2', 'English language'],
            ['9995501236', '99955-0-123-6', 'Srpska, Republic of'],
            ['9995523450', '99955-23-45-0', 'Srpska, Republic of'],
            ['999556789X', '99955-678-9-X', 'Srpska, Republic of'],

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

    /**
     * @dataProvider providerIsValidGroupAndRange
     *
     * @param string $isbnString
     * @param bool   $isValidGroup
     * @param bool   $isValidRange
     */
    public function testIsValidGroupAndRange(string $isbnString, bool $isValidGroup, bool $isValidRange) : void
    {
        $isbn = Isbn::of($isbnString);

        $this->assertSame($isValidGroup, $isbn->isValidGroup());
        $this->assertSame($isValidRange, $isbn->isValidRange());

        if (! $isValidGroup) {
            $this->assertException(IsbnException::class, function() use ($isbn) {
                $isbn->getGroupIdentifier();
            });
            $this->assertException(IsbnException::class, function() use ($isbn) {
                $isbn->getGroupName();
            });

            // ISBN with invalid group/range cannot be formatted
            $this->assertSame($isbnString, $isbn->format());
        }

        if (! $isValidRange) {

            $this->assertException(IsbnException::class, function() use ($isbn) {
                $isbn->getPublisherIdentifier();
            });
            $this->assertException(IsbnException::class, function() use ($isbn) {
                $isbn->getTitleIdentifier();
            });
            $this->assertException(IsbnException::class, function() use ($isbn) {
                $isbn->getParts();
            });
        }
    }

    /**
     * @return array
     */
    public function providerIsValidGroupAndRange() : array
    {
        return [
            ['0001234560', true, true],
            ['9983000008', true, false],
            ['9999999999', false, false],
            ['9780001234567', true, true],
            ['9789983000009', true, false],
            ['9789999999991', false, false],
        ];
    }

    /**
     * Asserts that an exception is thrown when calling the given function.
     *
     * @param string   $expectedException
     * @param callable $function
     *
     * @throws \Exception
     */
    private function assertException(string $expectedException, callable $function) : void
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

        $this->fail('Failed asserting that exception of type ' . $expectedException . ' is thrown.');
    }
}
