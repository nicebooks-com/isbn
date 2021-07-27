<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Internal;

use Nicebooks\Isbn\IsbnGroup;

/**
 * Internal utility class for ISBN formatting.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 * All input is expected to be validated.
 *
 * @psalm-type RangeType = array{string, string, string, list<array{int, string, string}>}
 *
 * @internal
 */
final class RangeService
{
    /**
     * @psalm-var list<RangeType>|null
     */
    private static ?array $ranges = null;

    /**
     * @psalm-return list<RangeType>
     */
    private static function getRanges() : array
    {
        if (self::$ranges === null) {
            self::$ranges = require __DIR__ . '/../../data/ranges.php';
        }

        return self::$ranges;
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getGroups(bool $is13) : array
    {
        $groups = [];

        foreach (self::getRanges() as $rangeData) {
            [$rangePrefix, $groupIdentifier, $groupName] = $rangeData;

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
     */
    public static function getRangeInfo(string $isbn) : ?RangeInfo
    {
        $length = strlen($isbn);
        $isbnPrefix = ($length === 10) ? '978' : substr($isbn, 0, 3);
        $isbnDigits = ($length === 10) ? $isbn : substr($isbn, 3);

        foreach (self::getRanges() as $rangeData) {
            [$eanPrefix, $groupIdentifier, $groupName, $ranges] = $rangeData;

            if ($isbnPrefix !== $eanPrefix) {
                continue;
            }

            $groupLength = strlen($groupIdentifier);
            $isbnGroup = substr($isbnDigits, 0, $groupLength);

            if ($isbnGroup !== $groupIdentifier) {
                continue;
            }

            $groupIdentifier = ($length === 10 ? $groupIdentifier : $eanPrefix . '-' . $groupIdentifier);

            $parts = null;

            foreach ($ranges as $range) {
                [$rangeLength, $rangeStart, $rangeEnd] = $range;
                $rangeValue = substr($isbnDigits, $groupLength, $rangeLength);
                $lastDigits = substr($isbnDigits, $groupLength + $rangeLength, -1);
                $checkDigit = substr($isbnDigits, -1);

                if (strcmp($rangeValue, $rangeStart) >= 0 && strcmp($rangeValue, $rangeEnd) <= 0) {
                    if ($length === 13) {
                        $parts = [$isbnPrefix, $isbnGroup, $rangeValue, $lastDigits, $checkDigit];
                    } else {
                        $parts = [$isbnGroup, $rangeValue, $lastDigits, $checkDigit];
                    }

                    break;
                }
            }

            return new RangeInfo($groupIdentifier, $groupName, $parts);
        }

        return null;
    }

    public static function format(string $isbn) : string
    {
        $rangeInfo = self::getRangeInfo($isbn);

        if ($rangeInfo !== null && $rangeInfo->parts !== null) {
            return implode('-', $rangeInfo->parts);
        }

        return $isbn;
    }
}
