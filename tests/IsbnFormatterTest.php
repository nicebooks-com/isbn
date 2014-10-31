<?php

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnTools;

/**
 * Unit tests for class IsbnTools formatting.
 */
class IsbnFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerFormat
     *
     * @param string $isbn           The input ISBN.
     * @param string $expectedOutput The expected formatted output.
     */
    public function testFormat($isbn, $expectedOutput)
    {
        $tools = new IsbnTools(true, false);
        $this->assertSame($expectedOutput, $tools->format($isbn));
    }

    /**
     * @return array
     */
    public function providerFormat()
    {
        return array(
            array('0001234567', '0-00-123456-7'),
            array('0321234567', '0-321-23456-7'),
            array('0765432109', '0-7654-3210-9'),
            array('0876543210', '0-87654-321-0'),
            array('0912345678', '0-912345-67-8'),
            array('0987654321', '0-9876543-2-1'),
            array('9995501234', '99955-0-123-4'),
            array('9995523456', '99955-23-45-6'),
            array('9995567890', '99955-678-9-0'),

            array('9780001234567', '978-0-00-123456-7'),
            array('9780321234567', '978-0-321-23456-7'),
            array('9780765432109', '978-0-7654-3210-9'),
            array('9780876543210', '978-0-87654-321-0'),
            array('9780912345678', '978-0-912345-67-8'),
            array('9780987654321', '978-0-9876543-2-1'),
            array('9789995501234', '978-99955-0-123-4'),
            array('9789995523456', '978-99955-23-45-6'),
            array('9789995567890', '978-99955-678-9-0'),
            array('9791001234567', '979-10-01-23456-7'),
            array('9791023456789', '979-10-234-5678-9'),
            array('9791078901234', '979-10-7890-123-4'),
            array('9791090123456', '979-10-90123-45-6'),
            array('9791098765432', '979-10-987654-3-2'),

            array('123456789x', '1-234-56789-X'),
            array(' #!1-2,3.456 789x ', '1-234-56789-X'),
            array('9781234567890', '978-1-234-56789-0'),
            array(' 978 1&2.3#4~5!6;7$8-90 ', '978-1-234-56789-0'),
        );
    }

    /**
     * @dataProvider providerFormatInvalidIsbnThrowsException
     * @expectedException \Nicebooks\Isbn\Exception\InvalidIsbnException
     *
     * @param string $isbn The invalid ISBN.
     */
    public function testFormatInvalidIsbnThrowsException($isbn)
    {
        $tools = new IsbnTools();
        $tools->format($isbn);
    }

    /**
     * @return array
     */
    public function providerFormatInvalidIsbnThrowsException()
    {
        return array(
            array('123456789'),
            array('12345678X9'),
            array('123456789A'),
            array('12345678901'),
            array('123456789012'),
            array('123456789012X'),
            array('12345678901234'),
        );
    }
}
