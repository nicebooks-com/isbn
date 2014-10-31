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

    /**
     * @dataProvider providerNotConvertibleThrowsException
     * @expectedException \Nicebooks\Isbn\Exception\IsbnNotConvertibleException
     *
     * @param string $isbn13 An ISBN-13 not convertible to ISBN-10.
     */
    public function testNotConvertibleThrowsException($isbn13)
    {
        $tools = new IsbnTools();
        $tools->convertIsbn13to10($isbn13);
    }

    /**
     * @return array
     */
    public function providerNotConvertibleThrowsException()
    {
        return array(
            array('9790123456785'),
            array('9799012345674')
        );
    }
}
