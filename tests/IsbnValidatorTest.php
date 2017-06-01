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
     * @param string $isbn    The ISBN to validate.
     * @param bool   $isValid The expected result.
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
        return [
            ['0123456789', true],
            ['012345678X', false],
            ['123456789X', true],
            ['1234567890', false],
            ['2345678909', true],
            ['234567890X', false],
            ['4567890124', true],
            ['4567890125', false],
            ['5678901230', true],
            ['5678901231', false],
            ['6789012346', true],
            ['6789012347', false],
            ['7890123450', true],
            ['7890123451', false],
            ['8901234564', true],
            ['8901234565', false],
            ['9012345677', true],
            ['9012345678', false],
            ['9876543202', true],
            ['9876543203,', false],
            ['9876543458', true],
            ['9876543459', false],
            ['9876542451', true],
            ['9876542452', false],
            ['9876542443', true],
            ['9876542444', false],
            ['9876542478', true],
            ['9876542479', false],
            ['9875446475', true],
            ['9875446476', false],

            ['9780123456786', true],
            ['9780123456787', false],
            ['9781234567897', true],
            ['9781234567898', false],
            ['9782345678908', true],
            ['9782345678909', false],
            ['9783456789019', true],
            ['9783456789010', false],
            ['9784567890120', true],
            ['9784567890121', false],
            ['9785678901231', true],
            ['9785678901232', false],
            ['9786789012342', true],
            ['9786789012343', false],
            ['9787890123453', true],
            ['9787890123454', false],
            ['9788901234564', true],
            ['9788901234565', false],
            ['9789012345675', true],
            ['9789012345676', false],

            ['9790123456785', true],
            ['9790123456786', false],
            ['9791234567896', true],
            ['9791234567897', false],
            ['9792345678907', true],
            ['9792345678908', false],
            ['9793456789018', true],
            ['9793456789019', false],
            ['9794567890129', true],
            ['9794567890120', false],
            ['9795678901230', true],
            ['9795678901231', false],
            ['9796789012341', true],
            ['9796789012342', false],
            ['9797890123452', true],
            ['9797890123453', false],
            ['9798901234563', true],
            ['9798901234564', false],
            ['9799012345674', true],
            ['9799012345675', false],
        ];
    }

    /**
     * @dataProvider providerIsValidIsbn10
     *
     * @param string $isbn                  The ISBN to validate.
     * @param bool   $cleanupBeforeValidate Whether to clean up the ISBN before validation.
     * @param bool   $validateCheckDigit    Whether to validate the check digit.
     * @param bool   $isValid               The expected result.
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
        return [
            ['123456789X', false, false, true],
            ['123456789X', false, true, true],
            ['123456789X', true, false, true],
            ['123456789X', true, true, true],

            ['123456789x', false, false, true],
            ['123456789x', false, true, true],
            ['123456789x', true, false, true],
            ['123456789x', true, true, true],

            ['1234567890', false, false, true],
            ['1234567890', false, true, false],
            ['1234567890', true, false, true],
            ['1234567890', true, true, false],

            [' 1-23456789 x ', false, false, false],
            [' 1-23456789 x ', false, true, false],
            [' 1-23456789 x ', true, false, true],
            [' 1-23456789 x ', true, true, true],

            [' 1-23456,789 0 ', false, false, false],
            [' 1-23456,789 0 ', false, true, false],
            [' 1-23456,789 0 ', true, false, true],
            [' 1-23456,789 0 ', true, true, false],
        ];
    }
    /**
     * @dataProvider providerIsValidIsbn13
     *
     * @param string $isbn                  The ISBN to validate.
     * @param bool   $cleanupBeforeValidate Whether to clean up the ISBN before validation.
     * @param bool   $validateCheckDigit    Whether to validate the check digit.
     * @param bool   $isValid               The expected result.
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
        return [
            ['9781234567897', false, false, true],
            ['9781234567897', false, true, true],
            ['9781234567897', true, false, true],
            ['9781234567897', true, true, true],

            ['9781234567890', false, false, true],
            ['9781234567890', false, true, false],
            ['9781234567890', true, false, true],
            ['9781234567890', true, true, false],

            [' 978-1234 567|897 ', false, false, false],
            [' 978-1234 567|897 ', false, true, false],
            [' 978-1234 567|897 ', true, false, true],
            [' 978-1234 567|897 ', true, true, true],

            [' 978-1234 567#890 ', false, false, false],
            [' 978-1234 567#890 ', false, true, false],
            [' 978-1234 567#890 ', true, false, true],
            [' 978-1234 567#890 ', true, true, false],
        ];
    }
}
