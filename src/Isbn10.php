<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

final class Isbn10 extends Isbn
{
    public static function of(string $isbn) : Isbn10
    {
        return parent::of($isbn)->to10();
    }

    public function is10() : bool
    {
        return true;
    }

    public function is13() : bool
    {
        return false;
    }

    public function isConvertibleTo10() : bool
    {
        return true;
    }

    public function to10() : Isbn10
    {
        return $this;
    }

    public function to13() : Isbn13
    {
        return self::newIsbn13(Internal\Converter::convertIsbn10to13($this->isbn));
    }
}
