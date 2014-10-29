<?php

namespace Nicebooks\Isbn\Exception;

/**
 * Exception thrown when an invalid ISBN is detected.
 */
class InvalidIsbnException extends IsbnException
{
    /**
     * @param string $isbn
     *
     * @return InvalidIsbnException
     */
    public static function forIsbn($isbn)
    {
        return new self(sprintf('"%s" is not a valid ISBN number.', $isbn));
    }
}
