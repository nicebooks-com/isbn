<?php

namespace Nicebooks\Isbn;

use Nicebooks\Isbn\Internal\Formatter;

class IsbnGroup
{
    /**
     * @var string
     */
    private $prefix;

    /**
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
        return Formatter::getGroups(false);
    }

    /**
     * @return IsbnGroup[]
     */
    public static function getIsbn13Groups()
    {
        return Formatter::getGroups(true);
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
