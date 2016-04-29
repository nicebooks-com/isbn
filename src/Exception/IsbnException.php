<?php

namespace Nicebooks\Isbn\Exception;

/**
 * Base class for all ISBN exceptions.
 */
class IsbnException extends \Exception
{
    /**
     * @param string $isbn
     *
     * @return IsbnException
     */
    public static function unknownRange($isbn)
    {
        return new IsbnException(sprintf('The ISBN %s is semantically valid, but not in a recognized range.', $isbn));
    }
}
