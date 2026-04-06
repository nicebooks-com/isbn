<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Exception;

/**
 * Exception thrown when an ISBN is semantically valid, but not in a recognized group or range.
 */
final class IsbnNotRecognizedException extends IsbnException
{
    public static function unknownGroup(string $isbn): IsbnNotRecognizedException
    {
        return new self(sprintf('The ISBN %s is semantically valid, but not in a recognized group.', $isbn));
    }

    public static function unknownRange(string $isbn): IsbnNotRecognizedException
    {
        return new self(sprintf(
            'The ISBN %s is semantically valid and belongs to a valid group, but is not in a recognized range.',
            $isbn,
        ));
    }
}
