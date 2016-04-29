<?php

namespace Nicebooks\Isbn\Internal;

/**
 * Internal class representing range info for an ISBN.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 *
 * @internal
 */
class IsbnRangeInfo
{
    /**
     * The parts of the ISBN number.
     *
     * ISBN-10 have 4 parts, ISBN-13 have 5 parts.
     *
     * @var array
     */
    public $parts;

    /**
     * The group name.
     *
     * @var string
     */
    public $groupName;
}
