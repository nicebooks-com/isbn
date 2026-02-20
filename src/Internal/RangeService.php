<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Internal;

use Nicebooks\Isbn\IsbnGroup;
use Nicebooks\Isbn\RegistrationGroup;

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
     * @var list<RangeType>|null
     */
    private static ?array $ranges = null;

    /**
     * @return list<RangeType>
     */
    private static function getRanges(): array
    {
        if (self::$ranges === null) {
            /** @var list<RangeType> $ranges */
            $ranges = require __DIR__ . '/../../data/ranges.php';

            self::$ranges = $ranges;
        }

        return self::$ranges;
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getGroups(bool $is13): array
    {
        $groups = [];

        foreach (self::getRanges() as [$rangePrefix, $groupIdentifier, $groupName]) {
            if ($is13) {
                // @mago-expect analyzer:deprecated-class
                $groups[] = new IsbnGroup($rangePrefix . '-' . $groupIdentifier, $groupName);
            } elseif ($rangePrefix === '978') {
                // @mago-expect analyzer:deprecated-class
                $groups[] = new IsbnGroup($groupIdentifier, $groupName);
            }
        }

        return $groups;
    }

    /**
     * @return list<RegistrationGroup>
     */
    public static function getRegistrationGroups(): array
    {
        $groups = [];

        foreach (self::getRanges() as [$rangePrefix, $groupIdentifier, $groupName]) {
            $groups[] = new RegistrationGroup($rangePrefix, $groupIdentifier, $groupName);
        }

        return $groups;
    }

    /**
     * Splits an ISBN into parts.
     *
     * @param string $isbn The ISBN-10 or ISBN-13, regexp-validated.
     */
    public static function getRangeInfo(string $isbn): ?RangeInfo
    {
        $length = strlen($isbn);
        $isbnPrefix = $length === 10 ? '978' : substr($isbn, 0, 3);
        $isbnDigits = $length === 10 ? $isbn : substr($isbn, 3);

        foreach (self::getRanges() as [$eanPrefix, $groupIdentifier, $groupName, $ranges]) {
            if ($isbnPrefix !== $eanPrefix) {
                continue;
            }

            $groupLength = strlen($groupIdentifier);
            $isbnGroup = substr($isbnDigits, 0, $groupLength);

            if ($isbnGroup !== $groupIdentifier) {
                continue;
            }

            $parts = null;

            foreach ($ranges as [$rangeLength, $rangeStart, $rangeEnd]) {
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

            $registrationGroup = new RegistrationGroup($eanPrefix, $groupIdentifier, $groupName);

            return new RangeInfo($registrationGroup, $parts);
        }

        return null;
    }
}
