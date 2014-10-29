<?php

namespace Nicebooks\Isbn;

/**
 * Validates ISBN numbers.
 */
class IsbnValidator
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
     * Returns whether the given ISBN is a valid ISBN-10 or ISBN-13.
     *
     * @param string $isbn The unformatted ISBN.
     *
     * @return boolean
     */
    public function isValidIsbn($isbn)
    {
        return $this->isValidIsbn10($isbn) || $this->isValidIsbn13($isbn);
    }

    /**
     * Returns whether the given ISBN is a valid ISBN-10.
     *
     * @param string $isbn The unformatted ISBN.
     *
     * @return boolean
     */
    public function isValidIsbn10($isbn)
    {
        $isbn = (string) $isbn;

        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            return false;
        }

        if ($this->configuration->cleanupBeforeValidate()) {
            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN10, $isbn) === 0) {
            return false;
        }

        if ($this->configuration->validateCheckDigit()) {
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
     * @return boolean
     */
    public function isValidIsbn13($isbn)
    {
        $isbn = (string) $isbn;

        if (preg_match(Internal\Regexp::ASCII, $isbn) === 0) {
            return false;
        }

        if ($this->configuration->cleanupBeforeValidate()) {
            $isbn = preg_replace(Internal\Regexp::NON_ALNUM, '', $isbn);
        }

        $isbn = strtoupper($isbn);

        if (preg_match(Internal\Regexp::ISBN13, $isbn) === 0) {
            return false;
        }

        if ($this->configuration->validateCheckDigit()) {
            if (! Internal\CheckDigit::validateCheckDigit13($isbn)) {
                return false;
            }
        }

        return true;
    }
}
