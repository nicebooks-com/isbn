<?php

namespace Nicebooks\Isbn\Internal;

use Nicebooks\Isbn\IsbnGroup;

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
     * @return array
     */
    private static function getRanges()
    {
        if (self::$ranges === null) {
            self::$ranges = require __DIR__ . '/../../data/ranges.php';
        }

        return self::$ranges;
    }

    /**
     * @param bool $is13
     *
     * @return IsbnGroup[]
     */
    public static function getGroups($is13)
    {
        $groups = array();

        foreach (self::getRanges() as $rangeData) {
            list ($rangePrefix, $groupIdentifier, $groupName) = $rangeData;

            if ($is13) {
                $groups[] = new IsbnGroup($rangePrefix . '-' . $groupIdentifier, $groupName);
            } elseif ($rangePrefix === '978') {
                $groups[] = new IsbnGroup($groupIdentifier, $groupName);
            }
        }

        return $groups;
    }

    /**
     * Splits an ISBN into parts.
     *
     * @param string $isbn The ISBN-10 or ISBN-13, regexp-validated.
     *
     * @return IsbnRangeInfo|null
     */
    public static function getRangeInfo($isbn)
    {
        $length = strlen($isbn);
        $prefix = ($length === 10) ? '978' : substr($isbn, 0, 3);
        $digits = ($length === 10) ? $isbn : substr($isbn, 3);

        foreach (self::getRanges() as $rangeData) {
            list ($rangePrefix, $groupIdentifier, $groupName, $ranges) = $rangeData;

            if ($prefix !== $rangePrefix) {
                continue;
            }

            $groupLength = strlen($groupIdentifier);
            $isbnGroup = substr($digits, 0, $groupLength);

            if ($isbnGroup !== $groupIdentifier) {
                continue;
            }

            $rangeInfo = new IsbnRangeInfo;
            $rangeInfo->groupIdentifier = $groupIdentifier;
            $rangeInfo->groupName = $groupName;

            foreach ($ranges as $range) {
                list ($rangeLength, $rangeStart, $rangeEnd) = $range;
                $rangeValue = substr($digits, $groupLength, $rangeLength);
                $lastDigits = substr($digits, $groupLength + $rangeLength, -1);
                $checkDigit = substr($digits, -1);

                if (strcmp($rangeValue, $rangeStart) >= 0 && strcmp($rangeValue, $rangeEnd) <= 0) {
                    if ($length === 13) {
                        $rangeInfo->parts = array($prefix, $isbnGroup, $rangeValue, $lastDigits, $checkDigit);
                    } else {
                        $rangeInfo->parts = array($isbnGroup, $rangeValue, $lastDigits, $checkDigit);
                    }

                    break;
                }
            }

            return $rangeInfo;
        }

        return null;
    }

    /**
     * @param string $isbn
     *
     * @return string
     */
    public static function format($isbn)
    {
        $rangeInfo = self::getRangeInfo($isbn);

        if ($rangeInfo !== null && $rangeInfo->parts !== null) {
            return implode('-', $rangeInfo->parts);
        }

        return $isbn;
    }
}
