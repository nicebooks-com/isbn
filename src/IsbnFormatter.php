<?php

namespace Nicebooks\Isbn;
use Nicebooks\Isbn\Exception\InvalidIsbnException;

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
     * Formats an ISBN number.
     *
     * @param string $isbn The ISBN-10 or ISBN-13 number.
     *
     * @return string The formatted ISBN number.
     *
     * @throws Exception\InvalidIsbnException If the ISBN is not valid.
     */
    public function format($isbn)
    {
        $isbn = (string) $isbn;

        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            throw Exception\InvalidIsbnException::forIsbn($isbn);
        }

        if ($this->configuration->cleanupBeforeValidate()) {
            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 1) {
            if ($this->configuration->validateCheckDigit()) {
                if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                    throw InvalidIsbnException::forIsbn($isbn);
                }
            }

            return Internal\Formatter::format13($isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 1) {
            if ($this->configuration->validateCheckDigit()) {
                if (! Internal\CheckDigit::validateCheckDigit10($isbn)) {
                    throw InvalidIsbnException::forIsbn($isbn);
                }
            }

            return Internal\Formatter::format10($isbn);
        }

        throw Exception\InvalidIsbnException::forIsbn($isbn);
    }
}
