<?php

declare(strict_types=1);

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
final class CheckDigit
{
    /**
     * @param string $isbn The partial ISBN-10, validated as a string starting with 9 digits.
     *
     * @return string The check digit, can be 'X'.
     */
    public static function calculateCheckDigit10(string $isbn) : string
    {
        for ($sum = 0, $i = 0; $i < 9; $i++) {
            $digit = (int) $isbn[$i];
            $sum += $digit * (1 + $i);
        }

        $sum %= 11;

        return ($sum === 10) ? 'X' : (string) $sum;
    }

    /**
     * @param string $isbn The partial ISBN-13, validated as a string starting with 12 digits.
     *
     * @return string The check digit.
     */
    public static function calculateCheckDigit13(string $isbn) : string
    {
        for ($sum = 0, $i = 0; $i < 12; $i++) {
            $digit = (int) $isbn[$i];
            $sum += $digit * (1 + 2 * ($i % 2));
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }

    /**
     * @param string $isbn The ISBN-10, unformatted, uppercase.
     */
    public static function validateCheckDigit10(string $isbn) : bool
    {
        return $isbn[9] === self::calculateCheckDigit10($isbn);
    }

    /**
     * @param string $isbn The ISBN-13, unformatted.
     */
    public static function validateCheckDigit13(string $isbn) : bool
    {
        return $isbn[12] === self::calculateCheckDigit13($isbn);
    }
}
