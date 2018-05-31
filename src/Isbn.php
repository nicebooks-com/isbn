<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Internal\RangeService;
use Nicebooks\Isbn\Internal\RangeInfo;

/**
 * Represents a valid ISBN number. This class is immutable.
 */
class Isbn
{
    /**
     * @var string
     */
    private $isbn;

    /**
     * @var bool
     */
    private $is13;

    /**
     * @var RangeInfo|null
     */
    private $rangeInfo;

    /**
     * @param string $isbn The unformatted ISBN number, validated.
     * @param bool   $is13 Whether this is an ISBN-13.
     */
    private function __construct(string $isbn, bool $is13)
    {
        $this->isbn = $isbn;
        $this->is13 = $is13;

        $this->rangeInfo = RangeService::getRangeInfo($isbn);
    }

    /**
     * @param string $isbn
     *
     * @return Isbn
     *
     * @throws Exception\InvalidIsbnException
     */
    public static function of(string $isbn) : Isbn
    {
        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 1) {
            if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            return new Isbn($isbn, true);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 1) {
            if (! Internal\CheckDigit::validateCheckDigit10($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            return new Isbn($isbn, false);
        }

        throw Exception\InvalidIsbnException::forIsbn($isbn);
    }

    /**
     * @return bool
     */
    public function is10() : bool
    {
        return ! $this->is13;
    }

    /**
     * @return bool
     */
    public function is13() : bool
    {
        return $this->is13;
    }

    /**
     * @return bool
     */
    public function isConvertibleTo10() : bool
    {
        if ($this->is13) {
            return substr($this->isbn, 0, 3) === '978';
        }

        return true;
    }

    /**
     * Returns a copy of this Isbn, converted to ISBN-10.
     *
     * @return Isbn The ISBN-10.
     *
     * @throws Exception\IsbnNotConvertibleException If this is an ISBN-13 not starting with 978.
     */
    public function to10() : Isbn
    {
        if (! $this->is13) {
            return $this;
        }

        return new Isbn(Internal\Converter::convertIsbn13to10($this->isbn), false);
    }

    /**
     * Returns a copy of this Isbn, converted to ISBN-13.
     *
     * @return Isbn The ISBN-13.
     */
    public function to13() : Isbn
    {
        if ($this->is13) {
            return $this;
        }

        return new Isbn(Internal\Converter::convertIsbn10to13($this->isbn), true);
    }

    /**
     * Returns whether this ISBN is in a recognized group.
     *
     * If this method returns true, the following methods will not throw an exception:
     *
     * - getGroupIdentifier()
     * - getGroupName()
     *
     * @return bool
     */
    public function isValidGroup() : bool
    {
        return $this->rangeInfo !== null;
    }

    /**
     * Returns whether this ISBN is in a recognized range.
     *
     * If this method returns false, we are unable to split the ISBN into parts, and format it with hyphens.
     * This would mean that either the ISBN number is wrong, or this version of the library is compiled against
     * an outdated data file from ISBN International.
     *
     * Note that this method returning true only means that the ISBN number is *potentially* valid,
     * but does not indicate in any way whether the ISBN number has been *assigned* to a book yet.
     *
     * If this method returns true, format() will return a hyphenated result,
     * and the following methods will not throw an exception:
     *
     * - getGroupIdentifier()
     * - getGroupName()
     * - getPublisherIdentifier()
     * - getTitleIdentifier()
     * - getParts()
     *
     * @return bool
     */
    public function isValidRange() : bool
    {
        return $this->rangeInfo !== null && $this->rangeInfo->parts !== null;
    }

    /**
     * Returns the group identifier.
     *
     * The group or country identifier identifies a national or geographic grouping of publishers.
     *
     * For ISBN-13, the identifier includes the GS1 (EAN) prefix, for example "978-2".
     * For the equivalent ISBN-10, the identifier would be "2".
     *
     * @return string
     *
     * @throws IsbnException If this ISBN is not in a recognized group.
     */
    public function getGroupIdentifier() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        return $this->rangeInfo->groupIdentifier;
    }

    /**
     * Returns the group name.
     *
     * @return string
     *
     * @throws IsbnException If this ISBN is not in a recognized group.
     */
    public function getGroupName() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        return $this->rangeInfo->groupName;
    }

    /**
     * Returns the publisher identifier.
     *
     * The publisher identifier identifies a particular publisher within a group.
     *
     * @return string
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    public function getPublisherIdentifier() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->rangeInfo->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->rangeInfo->parts[$this->is13 ? 2 : 1];
    }

    /**
     * Returns the title identifier.
     *
     * The title identifier identifies a particular title or edition of a title.
     *
     * @return string
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    public function getTitleIdentifier() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->rangeInfo->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->rangeInfo->parts[$this->is13 ? 3 : 2];
    }

    /**
     * Returns the check digit.
     *
     * The check digit is the single digit at the end of the ISBN which validates the ISBN.
     *
     * @return string
     */
    public function getCheckDigit() : string
    {
        return substr($this->isbn, -1);
    }

    /**
     * @return array
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    public function getParts() : array
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->rangeInfo->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->rangeInfo->parts;
    }

    /**
     * Returns the formatted (hyphenated) ISBN number.
     *
     * If the ISBN number is not in a recognized range, it is returned unformatted.
     *
     * @return string
     */
    public function format() : string
    {
        if ($this->rangeInfo === null || $this->rangeInfo->parts === null) {
            return $this->isbn;
        }

        return implode('-', $this->rangeInfo->parts);
    }

    /**
     * Returns the unformatted ISBN number.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->isbn;
    }
}
