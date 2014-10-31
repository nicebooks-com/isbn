<?php

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnTools;

/**
 * Unit tests for IsbnTools validation.
 */
class IsbnValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerIsValidIsbn
     *
     * @param string  $isbn    The ISBN to validate.
     * @param boolean $isValid The expected result.
     */
    public function testIsValidIsbn($isbn, $isValid)
    {
        $tools = new IsbnTools();
        $this->assertSame($isValid, $tools->isValidIsbn($isbn));
    }

    /**
     * @return array
     */
    public function providerIsValidIsbn()
    {
        return array(
            array('0123456789', true),
            array('012345678X', false),
            array('123456789X', true),
            array('1234567890', false),
            array('2345678909', true),
            array('234567890X', false),
            array('4567890124', true),
            array('4567890125', false),
            array('5678901230', true),
            array('5678901231', false),
            array('6789012346', true),
            array('6789012347', false),
            array('7890123450', true),
            array('7890123451', false),
            array('8901234564', true),
            array('8901234565', false),
            array('9012345677', true),
            array('9012345678', false),
            array('9876543202', true),
            array('9876543203,', false),
            array('9876543458', true),
            array('9876543459', false),
            array('9876542451', true),
            array('9876542452', false),
            array('9876542443', true),
            array('9876542444', false),
            array('9876542478', true),
            array('9876542479', false),
            array('9875446475', true),
            array('9875446476', false),

            array('9780123456786', true),
            array('9780123456787', false),
            array('9781234567897', true),
            array('9781234567898', false),
            array('9782345678908', true),
            array('9782345678909', false),
            array('9783456789019', true),
            array('9783456789010', false),
            array('9784567890120', true),
            array('9784567890121', false),
            array('9785678901231', true),
            array('9785678901232', false),
            array('9786789012342', true),
            array('9786789012343', false),
            array('9787890123453', true),
            array('9787890123454', false),
            array('9788901234564', true),
            array('9788901234565', false),
            array('9789012345675', true),
            array('9789012345676', false),

            array('9790123456785', true),
            array('9790123456786', false),
            array('9791234567896', true),
            array('9791234567897', false),
            array('9792345678907', true),
            array('9792345678908', false),
            array('9793456789018', true),
            array('9793456789019', false),
            array('9794567890129', true),
            array('9794567890120', false),
            array('9795678901230', true),
            array('9795678901231', false),
            array('9796789012341', true),
            array('9796789012342', false),
            array('9797890123452', true),
            array('9797890123453', false),
            array('9798901234563', true),
            array('9798901234564', false),
            array('9799012345674', true),
            array('9799012345675', false),
        );
    }

    /**
     * @dataProvider providerIsValidIsbn10
     *
     * @param string  $isbn                  The ISBN to validate.
     * @param boolean $cleanupBeforeValidate Whether to clean up the ISBN before validation.
     * @param boolean $validateCheckDigit    Whether to validate the check digit.
     * @param boolean $isValid               The expected result.
     */
    public function testIsValidIsbn10($isbn, $cleanupBeforeValidate, $validateCheckDigit, $isValid)
    {
        $tools = new IsbnTools($cleanupBeforeValidate, $validateCheckDigit);

        $this->assertSame($isValid, $tools->isValidIsbn($isbn));
        $this->assertSame($isValid, $tools->isValidIsbn10($isbn));
    }

    /**
     * @return array
     */
    public function providerIsValidIsbn10()
    {
        return array(
            array('123456789X', false, false, true),
            array('123456789X', false, true, true),
            array('123456789X', true, false, true),
            array('123456789X', true, true, true),

            array('123456789x', false, false, true),
            array('123456789x', false, true, true),
            array('123456789x', true, false, true),
            array('123456789x', true, true, true),

            array('1234567890', false, false, true),
            array('1234567890', false, true, false),
            array('1234567890', true, false, true),
            array('1234567890', true, true, false),

            array(' 1-23456789 x ', false, false, false),
            array(' 1-23456789 x ', false, true, false),
            array(' 1-23456789 x ', true, false, true),
            array(' 1-23456789 x ', true, true, true),

            array(' 1-23456,789 0 ', false, false, false),
            array(' 1-23456,789 0 ', false, true, false),
            array(' 1-23456,789 0 ', true, false, true),
            array(' 1-23456,789 0 ', true, true, false),
        );
    }
    /**
     * @dataProvider providerIsValidIsbn13
     *
     * @param string  $isbn                  The ISBN to validate.
     * @param boolean $cleanupBeforeValidate Whether to clean up the ISBN before validation.
     * @param boolean $validateCheckDigit    Whether to validate the check digit.
     * @param boolean $isValid               The expected result.
     */
    public function testIsValidIsbn13($isbn, $cleanupBeforeValidate, $validateCheckDigit, $isValid)
    {
        $tools = new IsbnTools($cleanupBeforeValidate, $validateCheckDigit);

        $this->assertSame($isValid, $tools->isValidIsbn($isbn));
        $this->assertSame($isValid, $tools->isValidIsbn13($isbn));
    }

    /**
     * @return array
     */
    public function providerIsValidIsbn13()
    {
        return array(
            array('9781234567897', false, false, true),
            array('9781234567897', false, true, true),
            array('9781234567897', true, false, true),
            array('9781234567897', true, true, true),

            array('9781234567890', false, false, true),
            array('9781234567890', false, true, false),
            array('9781234567890', true, false, true),
            array('9781234567890', true, true, false),

            array(' 978-1234 567|897 ', false, false, false),
            array(' 978-1234 567|897 ', false, true, false),
            array(' 978-1234 567|897 ', true, false, true),
            array(' 978-1234 567|897 ', true, true, true),

            array(' 978-1234 567#890 ', false, false, false),
            array(' 978-1234 567#890 ', false, true, false),
            array(' 978-1234 567#890 ', true, false, true),
            array(' 978-1234 567#890 ', true, true, false),
        );
    }
}
