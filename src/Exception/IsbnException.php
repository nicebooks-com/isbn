<?php

declare(strict_types=1);

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
    public static function unknownGroup(string $isbn) : IsbnException
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
    public static function unknownRange(string $isbn) : IsbnException
    {
        return new IsbnException(sprintf(
            'The ISBN %s is semantically valid and belongs to a valid group, but is not in a recognized range.',
            $isbn
        ));
    }
}
