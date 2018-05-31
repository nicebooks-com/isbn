<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Internal;

use Nicebooks\Isbn\Exception\IsbnNotConvertibleException;

/**
 * Internal utility class for ISBN formatting.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 * All input is expected to be validated.
 *
 * @internal
 */
class Converter
{
    /**
     * @param string $isbn The ISBN-10, unformatted, regexp-validated.
     *
     * @return string
     */
    public static function convertIsbn10to13(string $isbn) : string
    {
        $isbn = '978' . substr($isbn, 0, 9);

        return $isbn . CheckDigit::calculateCheckDigit13($isbn);
    }

    /**
     * @param string $isbn The ISBN-13, unformatted, regexp-validated.
     *
     * @return string
     *
     * @throws \Nicebooks\Isbn\Exception\IsbnNotConvertibleException
     */
    public static function convertIsbn13to10(string $isbn) : string
    {
        if (substr($isbn, 0, 3) !== '978') {
            throw IsbnNotConvertibleException::forIsbn($isbn);
        }

        $isbn = substr($isbn, 3, 9);

        return $isbn . CheckDigit::calculateCheckDigit10($isbn);
    }
}
