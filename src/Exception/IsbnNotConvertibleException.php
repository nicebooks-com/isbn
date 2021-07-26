<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Exception;

/**
 * Exception thrown when failing to convert an ISBN-13 to ISBN-10.
 */
final class IsbnNotConvertibleException extends IsbnException
{
    public static function forIsbn(string $isbn) : IsbnNotConvertibleException
    {
        return new self(sprintf('ISBN %s cannot be converted to an ISBN-10.', $isbn));
    }
}
