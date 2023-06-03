<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Internal\RangeService;

/**
 * Represents a national or geographic group of publishers.
 */
final class IsbnGroup
{
    /**
     * The group prefix.
     *
     * Example: "2" for ISBN-10, "978-2" for ISBN-13.
     */
    private readonly string $prefix;

    /**
     * The group name.
     *
     * Example: "French language".
     */
    private readonly string $name;

    public function __construct(string $prefix, string $name)
    {
        $this->prefix = $prefix;
        $this->name   = $name;
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getIsbn10Groups() : array
    {
        return RangeService::getGroups(false);
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getIsbn13Groups() : array
    {
        return RangeService::getGroups(true);
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
