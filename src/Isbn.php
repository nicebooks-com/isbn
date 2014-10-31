<?php

namespace Nicebooks\Isbn;

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
     * @var boolean
     */
    private $is13;

    /**
     * @param string  $isbn The unformatted ISBN number, validated.
     * @param boolean $is13 Whether this is an ISBN-13.
     */
    private function __construct($isbn, $is13)
    {
        $this->isbn = $isbn;
        $this->is13 = $is13;
    }

    /**
     * @param string $isbn
     *
     * @return Isbn
     *
     * @throws Exception\InvalidIsbnException
     */
    public static function get($isbn)
    {
        $isbn = (string) $isbn;

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
     * @return boolean
     */
    public function is10()
    {
        return ! $this->is13;
    }

    /**
     * @return boolean
     */
    public function is13()
    {
        return $this->is13;
    }

    /**
     * @return boolean
     */
    public function isConvertibleTo10()
    {
        if (! $this->is13) {
            return true;
        }

        return substr($this->isbn, 0, 3) === '978';
    }

    /**
     * Returns a copy of this Isbn, converted to ISBN-10.
     *
     * @return Isbn The ISBN-10.
     *
     * @throws Exception\IsbnNotConvertibleException If this is an ISBN-13 not starting with 978.
     */
    public function to10()
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
    public function to13()
    {
        if ($this->is13) {
            return $this;
        }

        return new Isbn(Internal\Converter::convertIsbn10to13($this->isbn), true);
    }

    /**
     * Returns the formatted (hyphenated) ISBN number.
     *
     * @return string
     */
    public function format()
    {
        return $this->is13
            ? Internal\Formatter::format13($this->isbn)
            : Internal\Formatter::format10($this->isbn);
    }

    /**
     * Returns the unformatted ISBN number.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->isbn;
    }
}
