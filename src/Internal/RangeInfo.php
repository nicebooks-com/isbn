<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Internal;

use Nicebooks\Isbn\RegistrationGroup;

/**
 * Internal class representing range info for an ISBN.
 *
 * This class is not part of the public API and can change at any time.
 * It is not intended to be used in projects consuming this library.
 *
 * @internal
 */
final readonly class RangeInfo
{
    public RegistrationGroup $registrationGroup;

    /**
     * The parts of the ISBN number.
     *
     * ISBN-10 have 4 parts, ISBN-13 have 5 parts.
     *
     * If the ISBN number belongs to a known group, but does not fall within a valid range,
     * this property will be null.
     *
     * @var list<string>|null
     */
    public ?array $parts;

    /**
     * @param list<string>|null $parts
     */
    public function __construct(RegistrationGroup $registrationGroup, ?array $parts)
    {
        $this->registrationGroup = $registrationGroup;
        $this->parts = $parts;
    }
}
