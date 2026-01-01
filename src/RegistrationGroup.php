<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Internal\RangeService;
use Stringable;

/**
 * Represents a country, geographic region, or language area, such as "978-1".
 */
final readonly class RegistrationGroup implements Stringable
{
    /**
     * The prefix, either "978" or "979".
     */
    public string $prefix;

    /**
     * The registration group identifier, such as "1".
     */
    public string $identifier;

    /**
     * The group name, such as "English language".
     */
    public string $name;

    public function __construct(string $prefix, string $identifier, string $name)
    {
        $this->prefix = $prefix;
        $this->identifier = $identifier;
        $this->name = $name;
    }

    /**
     * @return RegistrationGroup[]
     */
    public static function all(): array
    {
        return RangeService::getRegistrationGroups();
    }

    /**
     * Returns the registration group as a string, such as "978-1".
     */
    public function toString(): string
    {
        return $this->prefix . '-' . $this->identifier;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
