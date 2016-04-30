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
    public static function unknownGroup($isbn)
    {
        return new IsbnException(sprintf(
            'The ISBN %s is semantically valid, but not in a recognized group.',
            $isbn
        ));
    }

    /**
     * @param string $isbn
     *
     * @return IsbnException
     */
    public static function unknownRange($isbn)
    {
        return new IsbnException(sprintf(
            'The ISBN %s is semantically valid and belongs to a valid group, but is not in a recognized range.',
            $isbn
        ));
    }
}
