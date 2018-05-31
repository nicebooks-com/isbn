<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Internal;

/**
 * List of regular expressions.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 *
 * @internal
 */
class Regexp
{
    /**
     * Matches an uppercase, unformatted ISBN-10.
     */
    public const ISBN10 = '/^[0-9]{9}[0-9X]$/';

    /**
     * Matches an unformatted ISBN-13.
     */
    public const ISBN13 = '/^97[89][0-9]{10}$/';

    /**
     * Matches a 7-bit ASCII string.
     */
    public const ASCII = '/^[\x00-\x7f]+$/';

    /**
     * Matches any non-alphanumeric char.
     */
    public const NON_ALNUM = '/[^0-9a-zA-Z]/';
}
