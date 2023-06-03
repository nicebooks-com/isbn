<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

final class Isbn13 extends Isbn
{
    public static function of(string $isbn) : Isbn13
    {
        return parent::of($isbn)->to13();
    }

    public function is10() : bool
    {
        return false;
    }

    public function is13() : bool
    {
        return true;
    }

    public function isConvertibleTo10() : bool
    {
        return str_starts_with($this->isbn, '978');
    }

    public function to10() : Isbn10
    {
        return self::newIsbn10(Internal\Converter::convertIsbn13to10($this->isbn));
    }

    public function to13() : Isbn13
    {
        return $this;
    }
}
