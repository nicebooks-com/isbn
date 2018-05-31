<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

/**
 * Tools to work with ISBN numbers as plain strings.
 */
class IsbnTools
{
    /**
     * @var bool
     */
    private $cleanupBeforeValidate;

    /**
     * @var bool
     */
    private $validateCheckDigit;

    /**
     * @param bool $cleanupBeforeValidate
     * @param bool $validateCheckDigit
     */
    public function __construct(bool $cleanupBeforeValidate = true, bool $validateCheckDigit = true)
    {
        $this->cleanupBeforeValidate = $cleanupBeforeValidate;
        $this->validateCheckDigit    = $validateCheckDigit;
    }

    /**
     * Returns whether the given ISBN is a valid ISBN-10 or ISBN-13.
     *
     * @param string $isbn The unformatted ISBN.
     *
     * @return bool
     */
    public function isValidIsbn(string $isbn) : bool
    {
        return $this->isValidIsbn10($isbn) || $this->isValidIsbn13($isbn);
    }

    /**
     * Returns whether the given ISBN is a valid ISBN-10.
     *
     * @param string $isbn The unformatted ISBN.
     *
     * @return bool
     */
    public function isValidIsbn10(string $isbn) : bool
    {
        if ($this->cleanupBeforeValidate) {
            if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
                return false;
            }

            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 0) {
            return false;
        }

        if ($this->validateCheckDigit) {
            if (! Internal\CheckDigit::validateCheckDigit10($isbn)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns whether the given ISBN is a valid ISBN-13.
     *
     * @param string $isbn The unformatted ISBN.
     *
     * @return bool
     */
    public function isValidIsbn13(string $isbn) : bool
    {
        if ($this->cleanupBeforeValidate) {
            if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
                return false;
            }

            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 0) {
            return false;
        }

        if ($this->validateCheckDigit) {
            if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts an ISBN-10 to an ISBN-13.
     *
     * @param string $isbn The ISBN-10 to convert.
     *
     * @return string The converted, unformatted ISBN-13.
     *
     * @throws Exception\InvalidIsbnException If the ISBN is not a valid ISBN-10.
     */
    public function convertIsbn10to13(string $isbn) : string
    {
        if ($this->cleanupBeforeValidate) {
            if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->validateCheckDigit) {
            if (! Internal\CheckDigit::validateCheckDigit10($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }
        }

        return Internal\Converter::convertIsbn10to13($isbn);
    }

    /**
     * Converts an ISBN-13 to an ISBN-10.
     *
     * Only ISBN-13 numbers starting with 978 can be converted to an ISBN-10.
     * If the input ISBN is a valid ISBN-13 but does not start with 978, an exception is thrown.
     *
     * @param string $isbn The ISBN-13 to convert.
     *
     * @return string The converted, unformatted ISBN-10.
     *
     * @throws Exception\InvalidIsbnException        If the ISBN is not a valid ISBN-13.
     * @throws Exception\IsbnNotConvertibleException If the ISBN cannot be converted.
     */
    public function convertIsbn13to10(string $isbn) : string
    {
        if ($this->cleanupBeforeValidate) {
            if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->validateCheckDigit) {
            if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }
        }

        return Internal\Converter::convertIsbn13to10($isbn);
    }

    /**
     * Formats an ISBN number.
     *
     * @param string $isbn The ISBN-10 or ISBN-13 number.
     *
     * @return string The formatted ISBN number.
     *
     * @throws Exception\InvalidIsbnException If the ISBN is not valid.
     */
    public function format(string $isbn) : string
    {
        if ($this->cleanupBeforeValidate) {
            if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }

            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 1) {
            if ($this->validateCheckDigit) {
                if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                    throw Exception\InvalidIsbnException::forIsbn($isbn);
                }
            }

            return Internal\RangeService::format($isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 1) {
            if ($this->validateCheckDigit) {
                if (! Internal\CheckDigit::validateCheckDigit10($isbn)) {
                    throw Exception\InvalidIsbnException::forIsbn($isbn);
                }
            }

            return Internal\RangeService::format($isbn);
        }

        throw Exception\InvalidIsbnException::forIsbn($isbn);
    }
}
