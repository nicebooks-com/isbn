<?php

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Exception\IsbnNotConvertibleException;

/**
 * Converts between ISBN-10 and ISBN-13 representations.
 */
class IsbnConverter
{
    /**
     * @var IsbnConfiguration
     */
    private $configuration;

    /**
     * Class constructor.
     *
     * @param IsbnConfiguration|null $configuration
     */
    public function __construct(IsbnConfiguration $configuration = null)
    {
        $this->configuration = $configuration ? clone $configuration : new IsbnConfiguration();
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
    public function convertIsbn10to13($isbn)
    {
        $isbn = (string) $isbn;

        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->configuration->cleanupBeforeValidate()) {
            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->configuration->validateCheckDigit()) {
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
    public function convertIsbn13to10($isbn)
    {
        $isbn = (string) $isbn;

        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->configuration->cleanupBeforeValidate()) {
            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->configuration->validateCheckDigit()) {
            if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                throw Exception\InvalidIsbnException::forIsbn($isbn);
            }
        }

        return Internal\Converter::convertIsbn13to10($isbn);
    }
}
