<?php

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\Isbn;

/**
 * Unit tests for class Isbn.
 */
class IsbnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param Isbn    $isbn   The Isbn instance to test.
     * @param string  $string The expected string value of the ISBN.
     * @param boolean $is13   Whether the ISBN is expected to be an ISBN-13.
     */
    private function assertIsbnEquals(Isbn $isbn, $string, $is13)
    {
        $this->assertSame($string, (string) $isbn);
        $this->assertSame($is13, $isbn->is13());
        $this->assertSame(! $is13, $isbn->is10());
    }

    /**
     * @dataProvider providerGet
     *
     * @param string  $isbn   The input ISBN.
     * @param string  $string The expected string value of the resulting Isbn object.
     * @param boolean $is13   Whether the ISBN is expected to be an ISBN-13.
     */
    public function testGet($isbn, $string, $is13)
    {
        $this->assertIsbnEquals(Isbn::of($isbn), $string, $is13);
    }

    /**
     * @return array
     */
    public function providerGet()
    {
        return array(
            array(' 1-234-56789-x ', '123456789X', false),
            array(' 978-0-00-000000-2 ', '9780000000002', true),
        );
    }

    /**
     * @dataProvider providerGetInvalidIsbnThrowsException
     * @expectedException \Nicebooks\Isbn\Exception\InvalidIsbnException
     *
     * @param string $invalidIsbn The invalid ISBN.
     */
    public function testGetInvalidIsbnThrowsException($invalidIsbn)
    {
        Isbn::of($invalidIsbn);
    }

    /**
     * @return array
     */
    public function providerGetInvalidIsbnThrowsException()
    {
        return array(
            array('123456789'),
            array('1234567890'),
            array('12345678901'),
            array('123456789012'),
            array('1234567890123'),
            array('9780000000000'),
            array('9790000000000'),
        );
    }

    /**
     * @dataProvider providerTo10
     *
     * @param string $isbn13 The input ISBN-13.
     * @param string $isbn10 The expected ISBN-10 output.
     */
    public function testTo10($isbn13, $isbn10)
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
    public function providerTo10()
    {
        return array(
            array('9780123456786', '0123456789'),
            array('9781234567897', '123456789X'),
            array('9782345678908', '2345678909'),
            array('9783456789019', '3456789017'),
            array('9784567890120', '4567890124'),
            array('9785678901231', '5678901230'),
            array('9786789012342', '6789012346'),
            array('9787890123453', '7890123450'),
            array('9788901234564', '8901234564'),
            array('9789012345675', '9012345677'),
        );
    }

    public function test10to10ReturnsThis()
    {
        $isbn = Isbn::of('123456789X');
        $this->assertSame($isbn, $isbn->to10());
    }

    /**
     * @dataProvider providerIsConvertibleTo10
     *
     * @param string  $isbn          The ISBN to test.
     * @param boolean $isConvertible Whether the ISBN is convertible to an ISBN-10.
     */
    public function testIsConvertibleTo10($isbn, $isConvertible)
    {
        $this->assertSame($isConvertible, Isbn::of($isbn)->isConvertibleTo10());
    }

    /**
     * @return array
     */
    public function providerIsConvertibleTo10()
    {
        return array(
            array('0123456789', true),
            array('123456789X', true),
            array('9780123456786', true),
            array('9781234567897', true),
            array('9790000000001', false),
            array('9791234567896', false),
        );
    }

    /**
     * @dataProvider providerNotConvertibleTo10
     * @expectedException \Nicebooks\Isbn\Exception\IsbnNotConvertibleException
     *
     * @param string $isbn13 The non-convertible ISBN-13.
     */
    public function testNotConvertibleTo10ThrowsException($isbn13)
    {
        Isbn::of($isbn13)->to10();
    }

    /**
     * @return array
     */
    public function providerNotConvertibleTo10()
    {
        return array(
            array('9790000000001'),
            array('9791234567896'),
        );
    }

    /**
     * @dataProvider providerTo13
     *
     * @param string $isbn10 The input ISBN-10.
     * @param string $isbn13 The expected ISBN-13 output.
     */
    public function testTo13($isbn10, $isbn13)
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
    public function providerTo13()
    {
        return array(
            array('0123456789', '9780123456786'),
            array('123456789X', '9781234567897'),
            array('2345678909', '9782345678908'),
            array('3456789017', '9783456789019'),
            array('4567890124', '9784567890120'),
            array('5678901230', '9785678901231'),
            array('6789012346', '9786789012342'),
            array('7890123450', '9787890123453'),
            array('8901234564', '9788901234564'),
            array('9012345677', '9789012345675'),
        );
    }

    public function test13to13ReturnsThis()
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
    public function testInfoAndFormat($isbn, $expectedFormat, $expectedGroup)
    {
        $isbn = Isbn::of($isbn);
        $expectedParts = explode('-', $expectedFormat);

        $this->assertSame($expectedFormat, Isbn::of($isbn)->format());
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
    public function providerInfoAndFormat()
    {
        return array(
            array('0001234560', '0-00-123456-0', 'English language'),
            array('0321234561', '0-321-23456-1', 'English language'),
            array('0765432102', '0-7654-3210-2', 'English language'),
            array('0876543212', '0-87654-321-2', 'English language'),
            array('0912345675', '0-912345-67-5', 'English language'),
            array('0987654322', '0-9876543-2-2', 'English language'),
            array('9995501236', '99955-0-123-6', 'Srpska, Republic of'),
            array('9995523450', '99955-23-45-0', 'Srpska, Republic of'),
            array('999556789X', '99955-678-9-X', 'Srpska, Republic of'),

            array('9780001234567', '978-0-00-123456-7', 'English language'),
            array('9780321234568', '978-0-321-23456-8', 'English language'),
            array('9780765432100', '978-0-7654-3210-0', 'English language'),
            array('9780876543214', '978-0-87654-321-4', 'English language'),
            array('9780912345673', '978-0-912345-67-3', 'English language'),
            array('9780987654328', '978-0-9876543-2-8', 'English language'),
            array('9789995501235', '978-99955-0-123-5', 'Srpska, Republic of'),
            array('9789995523459', '978-99955-23-45-9', 'Srpska, Republic of'),
            array('9789995567897', '978-99955-678-9-7', 'Srpska, Republic of'),
            array('9791001234563', '979-10-01-23456-3', 'France'),
            array('9791023456783', '979-10-234-5678-3', 'France'),
            array('9791078901238', '979-10-7890-123-8', 'France'),
            array('9791090123458', '979-10-90123-45-8', 'France'),
            array('9791098765438', '979-10-987654-3-8', 'France'),
        );
    }
}
