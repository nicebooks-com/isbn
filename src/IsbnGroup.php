<?php

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Internal\RangeService;

/**
 * Represents a national or geographic group of publishers.
 */
class IsbnGroup
{
    /**
     * The group prefix.
     *
     * Example: "2" for ISBN-10, "978-2" for ISBN-13.
     *
     * @var string
     */
    private $prefix;

    /**
     * The group name.
     *
     * Example: "French language".
     *
     * @var string
     */
    private $name;

    /**
     * @param string $prefix
     * @param string $name
     */
    public function __construct($prefix, $name)
    {
        $this->prefix = $prefix;
        $this->name   = $name;
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getIsbn10Groups()
    {
        return RangeService::getGroups(false);
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getIsbn13Groups()
    {
        return RangeService::getGroups(true);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
