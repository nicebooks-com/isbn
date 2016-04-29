<?php

namespace Nicebooks\Isbn\Internal;

/**
 * Internal utility class for ISBN formatting.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 * All input is expected to be validated.
 *
 * @internal
 */
class Formatter
{
    /**
     * @var array|null
     */
    private static $ranges;

    /**
     * @param string $isbn The ISBN-10, unformatted, regexp-validated.
     *
     * @return string
     */
    public static function format10($isbn)
    {
        return self::format('978', $isbn, false);
    }

    /**
     * @param string $isbn The ISBN-13, unformatted, regexp-validated.
     *
     * @return string
     */
    public static function format13($isbn)
    {
        return self::format(substr($isbn, 0, 3), substr($isbn, 3), true);
    }

    /**
     * Splits an ISBN into parts.
     *
     * @param string $isbn The ISBN-10 or ISBN-13, regexp-validated.
     *
     * @return array|null An array containing 4 (ISBN-10) or 5 (ISBN-13) parts, or null if not in a recognized range.
     */
    public static function getParts($isbn)
    {
        if (self::$ranges === null) {
            self::$ranges = require __DIR__ . '/../../data/ranges.php';
        }

        $length = strlen($isbn);
        $prefix = ($length === 10) ? '978' : substr($isbn, 0, 3);
        $digits = ($length === 10) ? $isbn : substr($isbn, 3);

        $groups = self::$ranges[$prefix];

        foreach ($groups as $group => $ranges) {
            $groupLength = strlen($group);
            $isbnGroup = substr($digits, 0, $groupLength);

            if ($isbnGroup == $group) {
                foreach ($ranges as $range) {
                    list ($rangeLength, $rangeStart, $rangeEnd) = $range;
                    $rangeValue = substr($digits, $groupLength, $rangeLength);
                    $lastDigits = substr($digits, $groupLength + $rangeLength, -1);
                    $checkDigit = substr($digits, -1);

                    if (strcmp($rangeValue, $rangeStart) >= 0 && strcmp($rangeValue, $rangeEnd) <= 0) {
                        if ($length === 13) {
                            return array($prefix, $isbnGroup, $rangeValue, $lastDigits, $checkDigit);
                        } else {
                            return array($isbnGroup, $rangeValue, $lastDigits, $checkDigit);
                        }
                    }
                }
            }
        }

        // Return the unformatted ISBN if no rule has been matched.
        return null;
    }

    /**
     * @param string  $prefix The ISBN prefix, 978 or 979. Always 978 for ISBN-10.
     * @param string  $digits The 10 relevant digits after the prefix (or full ISBN for ISBN-10).
     * @param boolean $is13   Whether we're dealing with an ISBN-13 (true) or ISBN-10 (false).
     *
     * @return string
     */
    private static function format($prefix, $digits, $is13)
    {
        if (self::$ranges === null) {
            self::$ranges = require __DIR__ . '/../../data/ranges.php';
        }

        $groups = self::$ranges[$prefix];

        foreach ($groups as $group => $ranges) {
            $groupLength = strlen($group);
            $isbnGroup = substr($digits, 0, $groupLength);

            if ($isbnGroup == $group) {
                foreach ($ranges as $range) {
                    list ($rangeLength, $rangeStart, $rangeEnd) = $range;
                    $rangeValue = substr($digits, $groupLength, $rangeLength);
                    $lastDigits = substr($digits, $groupLength + $rangeLength, -1);
                    $checkDigit = substr($digits, -1);

                    if (strcmp($rangeValue, $rangeStart) >= 0 && strcmp($rangeValue, $rangeEnd) <= 0) {
                        if ($is13) {
                            return sprintf('%s-%s-%s-%s-%s', $prefix, $isbnGroup, $rangeValue, $lastDigits, $checkDigit);
                        } else {
                            return sprintf('%s-%s-%s-%s', $isbnGroup, $rangeValue, $lastDigits, $checkDigit);
                        }
                    }
                }
            }
        }

        // Return the unformatted ISBN if no rule has been matched.
        return $is13 ? $prefix . $digits : $digits;
    }
}
