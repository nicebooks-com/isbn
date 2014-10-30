<?php

namespace Nicebooks\Isbn\Exception;

/**
 * Exception thrown when failing to convert an ISBN-13 to ISBN-10.
 */
class IsbnNotConvertibleException extends IsbnException
{
    /**
     * @param string $isbn
     *
     * @return IsbnNotConvertibleException
     */
    public static function forIsbn($isbn)
    {
        return new self(sprintf('ISBN %s cannot be converted to an ISBN-10.', $isbn));
    }
}
