<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Internal;

/**
 * Internal class representing range info for an ISBN.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 *
 * @psalm-immutable
 *
 * @internal
 */
final class RangeInfo
{
    public string $groupIdentifier;

    /**
     * The group name.
     */
    public string $groupName;

    /**
     * The parts of the ISBN number.
     *
     * ISBN-10 have 4 parts, ISBN-13 have 5 parts.
     *
     * If the ISBN number belongs to a known group, but does not fall within a valid range,
     * this property will be null.
     *
     * @var string[]|null
     */
    public ?array $parts;

    /**
     * @param string[]|null $parts
     */
    public function __construct(string $groupIdentifier, string $groupName, ?array $parts)
    {
        $this->groupIdentifier = $groupIdentifier;
        $this->groupName       = $groupName;
        $this->parts           = $parts;
    }
}
