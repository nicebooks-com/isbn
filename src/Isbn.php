<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Exception\IsbnException;
use Nicebooks\Isbn\Internal\RangeService;
use Override;
use Stringable;

/**
 * Represents an immutable ISBN number.
 *
 * Instances of this class always have valid digits, length, and check digit. This basic validation does not depend on
 * the version of the ISBN International data file used by this version of nicebooks/isbn.
 *
 * Full validation, which does depend on the data file version, can be performed with `isValid()`.
 */
abstract readonly class Isbn implements Stringable
{
    protected string $isbn;

    private ?RegistrationGroup $registrationGroup;

    /**
     * @var list<string>|null
     */
    private ?array $parts;

    /**
     * @param string $isbn The unformatted ISBN number, validated.
     */
    private function __construct(string $isbn)
    {
        $this->isbn = $isbn;
        $rangeInfo = RangeService::getRangeInfo($isbn);

        $this->registrationGroup = $rangeInfo?->registrationGroup;
        $this->parts = $rangeInfo?->parts;
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
    public static function of(string $isbn): Isbn
    {
        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        assert($isbn !== null);

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 1) {
            if (!Internal\CheckDigit::validateCheckDigit13($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            return new Isbn13($isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 1) {
            if (!Internal\CheckDigit::validateCheckDigit10($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            return new Isbn10($isbn);
        }

        throw Exception\InvalidIsbnException::forIsbn($isbn);
    }

    abstract public function is10(): bool;

    abstract public function is13(): bool;

    abstract public function isConvertibleTo10(): bool;

    /**
     * Returns a copy of this Isbn, converted to ISBN-10.
     *
     * @throws Exception\IsbnNotConvertibleException If this is an ISBN-13 not starting with 978.
     */
    abstract public function to10(): Isbn10;

    /**
     * Returns a copy of this Isbn, converted to ISBN-13.
     */
    abstract public function to13(): Isbn13;

    /**
     * Returns whether this ISBN belongs to a known group.
     *
     * This method only validates the group. For a full group and range validation, use isValid().
     *
     * When this method returns true, the following methods do not throw an exception:
     *
     * - getRegistrationGroup()
     * - getGroupIdentifier()
     * - getGroupName()
     */
    final public function hasValidRegistrationGroup(): bool
    {
        return $this->registrationGroup !== null;
    }

    /**
     * Returns whether this ISBN belongs to a known registration group and range.
     *
     * If this method returns true, we are able to split the ISBN into parts and format it with hyphens.
     * If it returns false, the ISBN number is not formattable; it means that either the ISBN number is invalid, or that
     * this version of the library is compiled against an outdated data file from ISBN International.
     *
     * Note that this method returning true only means that the ISBN number is potentially valid, but does not indicate
     * in any way whether the ISBN number has been assigned to a book yet.
     *
     * This is the highest level of validation that can be performed by looking at the ISBN number alone.
     *
     * When this method returns true, toFormattedString() returns a hyphenated result, and the following methods do not
     * throw an exception:
     *
     * - getRegistrationGroup()
     * - getGroupIdentifier()
     * - getGroupName()
     * - getPublisherIdentifier()
     * - getTitleIdentifier()
     * - getParts()
     */
    final public function isValid(): bool
    {
        return $this->parts !== null;
    }

    /**
     * @throws IsbnException If this ISBN is not in a recognized group.
     */
    final public function getRegistrationGroup(): RegistrationGroup
    {
        if ($this->registrationGroup === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        return $this->registrationGroup;
    }

    /**
     * Returns the group identifier which identifies a country, geographic region, or language area.
     *
     * Example for ISBN-10: "1-338-87893-X" => "1"
     * Example for ISBN-13: "978-1-338-87893-6" => "978-1"
     *
     * @throws IsbnException If this ISBN is not in a recognized group.
     *
     * @deprecated Use getRegistrationGroup()->prefix, ->identifier, and ->toString() instead.
     */
    final public function getGroupIdentifier(): string
    {
        if ($this->registrationGroup === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        return $this->is10() ? $this->registrationGroup->identifier : $this->registrationGroup->toString();
    }

    /**
     * Returns the English name of the country, geographic region, or language area that matches the group identifier.
     *
     * Examples: "English Language", "French language", "Japan", "Spain".
     *
     * @throws IsbnException If this ISBN is not in a recognized group.
     *
     * @deprecated Use getRegistrationGroup()->name instead.
     */
    final public function getGroupName(): string
    {
        if ($this->registrationGroup === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        return $this->registrationGroup->name;
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
    final public function getPublisherIdentifier(): string
    {
        if ($this->registrationGroup === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->parts[$this->is13() ? 2 : 1];
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
    final public function getTitleIdentifier(): string
    {
        if ($this->registrationGroup === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->parts[$this->is13() ? 3 : 2];
    }

    /**
     * Returns the check digit.
     *
     * Example for ISBN-10: "1-338-87893-X" => "X"
     * Example for ISBN-13: "978-1-338-87893-6" => "6"
     *
     * The check digit is the single digit at the end of the ISBN which validates the ISBN.
     */
    final public function getCheckDigit(): string
    {
        return $this->isbn[-1];
    }

    /**
     * Returns the parts that constitute this ISBN number, as an array of strings.
     *
     * ISBN-10 have 4 parts, ISBN-13 have 5 parts.
     *
     * Example for ISBN-10: "1-338-87893-X" => ["1", "338", "87893", "X"]
     * Example for ISBN-13: "978-1-338-87893-6" => ["978", "1", "338", "87893", "6"]
     *
     * @return list<string>
     *
     * @throws IsbnException If this ISBN is not in a recognized group or range.
     */
    final public function getParts(): array
    {
        if ($this->registrationGroup === null) {
            throw IsbnException::unknownGroup($this->isbn);
        }

        if ($this->parts === null) {
            throw IsbnException::unknownRange($this->isbn);
        }

        return $this->parts;
    }

    /**
     * Checks if this ISBN is equal to another ISBN.
     *
     * An ISBN-10 is considered equal to its corresponding ISBN-13.
     * For example, `Isbn::of('978-0-399-16534-4')->isEqualTo(Isbn::of("0-399-16534-7"))` returns true.
     */
    final public function isEqualTo(Isbn $otherIsbn): bool
    {
        return $this->to13()->isbn === $otherIsbn->to13()->isbn;
    }

    /**
     * Returns the unformatted ISBN number.
     */
    final public function toString(): string
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
    final public function toFormattedString(): string
    {
        if ($this->parts === null) {
            return $this->isbn;
        }

        return implode('-', $this->parts);
    }

    #[Override]
    final public function __toString(): string
    {
        return $this->isbn;
    }
}
