<?php

namespace Nicebooks\Isbn;

/**
 * Formats ISBN numbers.
 *
 * This class adds hyphens to an ISBN number at the proper places,
 * as defined by the range file published by ISBN international.
 *
 * This class is permissive on the input format, and does not validate the check digit of the ISBN.
 */
class IsbnFormatter
{
    /**
     * @var array
     */
    private $ranges;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->ranges = require __DIR__ . '/../data/ranges.php';
    }

    /**
     * Formats an ISBN number.
     *
     * @param string $isbn The ISBN-10 or ISBN-13 number.
     *
     * @return string The formatted ISBN number.
     *
     * @throws \InvalidArgumentException If the ISBN is not valid.
     */
    public function format($isbn)
    {
        $isbn = (string) $isbn;

        if (preg_match('/[\x80-\xff]/', $isbn) !== 0) {
            throw $this->invalidIsbnNumber($isbn);
        }

        $isbn = preg_replace('/[^0-9a-zA-Z]/', '', $isbn);
        $length = strlen($isbn);

        if ($length === 10) {
            if (preg_match('/^[0-9]{9}[0-9xX]$/', $isbn) === 1) {
                return $this->doFormat('978', $isbn[9] === 'x' ? strtoupper($isbn) : $isbn, false);
            }
        }

        if ($length === 13) {
            if (preg_match('/^[0-9]{13}$/', $isbn) === 1) {
                return $this->doFormat(substr($isbn, 0, 3), substr($isbn, 3), true);
            }
        }

        throw $this->invalidIsbnNumber($isbn);
    }

    /**
     * @param string  $prefix The ISBN prefix, 978 or 979. Always 978 for ISBN-10.
     * @param string  $digits The 10 relevant digits after the prefix (or full ISBN for ISBN-10).
     * @param boolean $is13   Whether we're dealing with an ISBN-13 (true) or ISBN-10 (false).
     *
     * @return string
     */
    private function doFormat($prefix, $digits, $is13)
    {
        $groups = $this->ranges[$prefix];

        foreach ($groups as $group => $ranges) {
            $groupLength = strlen($group);
            $isbnGroup = substr($digits, 0, $groupLength);

            if ($isbnGroup == $group) {
                foreach ($ranges as $range) {
                    list ($rangeLength, $rangeStart, $rangeEnd) = $range;
                    $rangeValue = substr($digits, $groupLength, $rangeLength);
                    $lastDigits = substr($digits, $groupLength + $rangeLength, -1);
                    $checkDigit = substr($digits, -1);

                    if ($rangeValue >= $rangeStart && $rangeValue <= $rangeEnd) {
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

    /**
     * @param string $isbn
     *
     * @return \InvalidArgumentException
     */
    private function invalidIsbnNumber($isbn)
    {
        return new \InvalidArgumentException(sprintf('"%s" is not a valid ISBN number.', $isbn));
    }
}
