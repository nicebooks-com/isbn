<?php

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnTools;

/**
 * Unit tests for class IsbnTools conversion.
 */
class IsbnConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerConvertIsbn10to13
     *
     * @param string $isbn10 The input ISBN-10.
     * @param string $isbn13 The expected output ISBN-13.
     */
    public function testConvertIsbn10to13($isbn10, $isbn13)
    {
        $tools = new IsbnTools();
        $this->assertSame($isbn13, $tools->convertIsbn10to13($isbn10));
    }

    /**
     * @return array
     */
    public function providerConvertIsbn10to13()
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

    /**
     * @dataProvider providerConvertInvalidIsbn10to13
     * @expectedException \Nicebooks\Isbn\Exception\InvalidIsbnException
     *
     * @param string $isbn
     */
    public function testConvertInvalidIsbn10to13($isbn)
    {
        $tools = new IsbnTools();
        $tools->convertIsbn10to13($isbn);
    }

    /**
     * @return array
     */
    public function providerConvertInvalidIsbn10to13()
    {
        return [
            ["0123456789\x80"],
            ['X123456789'],
            ['0123456788'],
        ];
    }

    /**
     * @dataProvider providerConvertIsbn13to10
     *
     * @param string $isbn13 The input ISBN-13.
     * @param string $isbn10 The expected output ISBN-10.
     */
    public function testConvertIsbn13to10($isbn13, $isbn10)
    {
        $tools = new IsbnTools();
        $this->assertSame($isbn10, $tools->convertIsbn13to10($isbn13));
    }

    /**
     * @return array
     */
    public function providerConvertIsbn13to10()
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

    /**
     * @dataProvider providerConvertInvalidIsbn13to10
     * @expectedException \Nicebooks\Isbn\Exception\InvalidIsbnException
     *
     * @param string $isbn
     */
    public function testConvertInvalidIsbn13to10($isbn)
    {
        $tools = new IsbnTools();
        $tools->convertIsbn13to10($isbn);
    }

    /**
     * @return array
     */
    public function providerConvertInvalidIsbn13to10()
    {
        return [
            ["9780123456786\x80"],
            ['9770123456786'],
            ['9780123456789'],
        ];
    }

    /**
     * @dataProvider providerIsbnNotConvertibleThrowsException
     * @expectedException \Nicebooks\Isbn\Exception\IsbnNotConvertibleException
     *
     * @param string $isbn An ISBN-13 not convertible to ISBN-10.
     */
    public function testIsbnNotConvertibleThrowsException($isbn)
    {
        $tools = new IsbnTools();
        $tools->convertIsbn13to10($isbn);
    }

    /**
     * @return array
     */
    public function providerIsbnNotConvertibleThrowsException()
    {
        return [
            ['9790123456785'],
            ['9799012345674']
        ];
    }
}
