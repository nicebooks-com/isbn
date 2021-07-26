<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Exception;

/**
 * Exception thrown when an invalid ISBN is detected.
 */
final class InvalidIsbnException extends IsbnException
{
    public static function forIsbn(string $isbn) : InvalidIsbnException
    {
        return new self(sprintf('"%s" is not a valid ISBN number.', $isbn));
    }
}
