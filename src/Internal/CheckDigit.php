<?php

namespace Nicebooks\Isbn\Internal;

/**
 * Internal utility class for ISBN check digits.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 * All input is expected to be validated.
 *
 * @internal
 */
class CheckDigit
{
    /**
     * @param string $isbn The partial ISBN-10, validated as a string starting with 9 digits.
     *
     * @return string The check digit, can be 'X'.
     */
    public static function calculateCheckDigit10($isbn)
    {
        for ($sum = 0, $i = 0; $i < 9; $i++) {
            $sum += $isbn[$i] * (1 + $i);
        }

        $sum %= 11;

        return ($sum === 10) ? 'X' : (string) $sum;
    }

    /**
     * @param string $isbn The partial ISBN-13, validated as a string starting with 12 digits.
     *
     * @return string The check digit.
     */
    public static function calculateCheckDigit13($isbn)
    {
        for ($sum = 0, $i = 0; $i < 12; $i++) {
            $sum += $isbn[$i] * (1 + 2 * ($i % 2));
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }

    /**
     * @param string $isbn The ISBN-10, unformatted, uppercase.
     *
     * @return boolean
     */
    public static function validateCheckDigit10($isbn)
    {
        return $isbn[9] === self::calculateCheckDigit10($isbn);
    }

    /**
     * @param string $isbn The ISBN-13, unformatted.
     *
     * @return boolean
     */
    public static function validateCheckDigit13($isbn)
    {
        return $isbn[12] === self::calculateCheckDigit13($isbn);
    }
}
