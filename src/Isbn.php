<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Internal\RangeService;
use Nicebooks\Isbn\Internal\RangeInfo;
use Override;
use Stringable;

/**
 * Represents an ISBN number with a valid checksum. This class is immutable.
 */
abstract readonly class Isbn implements Stringable
{
    protected string $isbn;

    private ?RangeInfo $rangeInfo;

    /**
     * @param string $isbn The unformatted ISBN number, validated.
     */
    private function __construct(string $isbn)
    {
        $this->isbn = $isbn;
        $this->rangeInfo = RangeService::getRangeInfo($isbn);
    }

    /**
     * Proxy method to create Isbn10 instances from within the Isbn13 class.
     */
    final protected function newIsbn10(string $isbn): Isbn10
    {
        return new Isbn10($isbn);
    }

    /**
     * Proxy method to create Isbn13 instances from within the Isbn10 class.
     */
    final protected function newIsbn13(string $isbn): Isbn13
    {
        return new Isbn13($isbn);
    }

    /**
     * @throws Exception\InvalidIsbnException If the ISBN is not valid.
     * @throws Exception\IsbnNotConvertibleException If called on the Isbn10 class, and an ISBN-13 is passed.
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

            return new Isbn13($isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 1) {
            if (! Internal\CheckDigit::validateCheckDigit10($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            return new Isbn10($isbn);
        }

        throw Exception\InvalidIsbnException::forIsbn($isbn);
    }

    abstract public function is10() : bool;

    abstract public function is13() : bool;

    abstract public function isConvertibleTo10() : bool;

    /**
     * Returns a copy of this Isbn, converted to ISBN-10.
     *
     * @throws Exception\IsbnNotConvertibleException If this is an ISBN-13 not starting with 978.
     */
    abstract public function to10() : Isbn10;

    /**
     * Returns a copy of this Isbn, converted to ISBN-13.
     */
    abstract public function to13() : Isbn13;

    /**
     * Returns whether this ISBN is in a recognized group.
     *
     * If this method returns true, the following methods will not throw an exception:
     *
     * - getGroupIdentifier()
     * - getGroupName()
     */
    final public function isValidGroup() : bool
    {
        return $this->rangeInfo !== null;
    }

    /**
     * Returns whether this ISBN is in a recognized range.
     *
     * If this method returns false, we are unable to split the ISBN into parts, and format it with hyphens.
     * This would mean that either the ISBN number is invalid, or this version of the library is compiled against
     * an outdated data file from ISBN International.
     *
     * Note that this method returning true only means that the ISBN number is *potentially* valid,
     * but does not indicate in any way whether the ISBN number has been *assigned* to a book yet.
     *
     * If this method returns true, toFormattedString() will return a hyphenated result,
     * and the following methods will not throw an exception:
     *
     * - getGroupIdentifier()
     * - getGroupName()
     * - getPublisherIdentifier()
     * - getTitleIdentifier()
     * - getParts()
     */
    final public function isValidRange() : bool
    {
        return $this->rangeInfo !== null && $this->rangeInfo->parts !== null;
    }

    /**
     * Returns the group identifier which identifies a country, geographic region, or language area.
     *
     * Example for ISBN-10: "1-338-87893-X" => "1"
     * Example for ISBN-13: "978-1-338-87893-6" => "978-1"
     *
     * @throws IsbnException If this ISBN is not in a recognized group.
     */
    final public function getGroupIdentifier() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        return $this->rangeInfo->groupIdentifier;
    }

    /**
     * Returns the English name of the country, geographic region, or language area that matches the group identifier.
     *
     * Examples: "English Language", "French language", "Japan", "Spain".
     *
     * @throws IsbnException If this ISBN is not in a recognized group.
     */
    final public function getGroupName() : string
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
     * Example for ISBN-10: "1-338-87893-X" => "338"
     * Example for ISBN-13: "978-1-338-87893-6" => "338"
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    final public function getPublisherIdentifier() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->rangeInfo->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->rangeInfo->parts[$this->is13() ? 2 : 1];
    }

    /**
     * Returns the title identifier.
     *
     * The title identifier identifies a particular title or edition of a title.
     *
     * Example for ISBN-10: "1-338-87893-X" => "87893"
     * Example for ISBN-13: "978-1-338-87893-6" => "87893"
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    final public function getTitleIdentifier() : string
    {
        if ($this->rangeInfo === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->rangeInfo->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->rangeInfo->parts[$this->is13() ? 3 : 2];
    }

    /**
     * Returns the check digit.
     *
     * Example for ISBN-10: "1-338-87893-X" => "X"
     * Example for ISBN-13: "978-1-338-87893-6" => "6"
     *
     * The check digit is the single digit at the end of the ISBN which validates the ISBN.
     */
    final public function getCheckDigit() : string
    {
        return $this->isbn[-1];
    }

    /**
     * Returns the parts that constitute this ISBN number, as an array of strings.
     *
     * Example for ISBN-10: "1-338-87893-X" => ["1", "338", "87893", "X"]
     * Example for ISBN-13: "978-1-338-87893-6" => ["978", "1", "338", "87893", "6"]
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    final public function getParts() : array
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
     * @deprecated Use toFormattedString() instead.
     */
    final public function format() : string
    {
        return $this->toFormattedString();
    }

    /**
     * Checks if this ISBN is equal to another ISBN.
     *
     * An ISBN-10 is considered equal to its corresponding ISBN-13.
     * For example, `Isbn::of('978-0-399-16534-4')->isEqualTo(Isbn::of("0-399-16534-7"))` returns true.
     */
    final public function isEqualTo(Isbn $otherIsbn) : bool
    {
        return $this->to13()->isbn === $otherIsbn->to13()->isbn;
    }

    /**
     * Returns the unformatted ISBN number.
     */
    final public function toString() : string
    {
        return $this->isbn;
    }

    /**
     * Returns the formatted (hyphenated) ISBN number.
     *
     * If the ISBN number is not in a recognized range, it is returned unformatted.
     *
     * Example for ISBN-10: "1-338-87893-X"
     * Example for ISBN-13: "978-1-338-87893-6"
     */
    final public function toFormattedString() : string
    {
        if ($this->rangeInfo === null || $this->rangeInfo->parts === null) {
            return $this->isbn;
        }

        return implode('-', $this->rangeInfo->parts);
    }

    #[Override]
    final public function __toString() : string
    {
        return $this->isbn;
    }
}
