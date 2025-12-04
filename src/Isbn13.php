<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Override;

final readonly class Isbn13 extends Isbn
{
    #[Override]
    public static function of(string $isbn) : Isbn13
    {
        return parent::of($isbn)->to13();
    }

    #[Override]
    public function is10() : bool
    {
        return false;
    }

    #[Override]
    public function is13() : bool
    {
        return true;
    }

    #[Override]
    public function isConvertibleTo10() : bool
    {
        return str_starts_with($this->isbn, '978');
    }

    #[Override]
    public function to10() : Isbn10
    {
        return self::newIsbn10(Internal\Converter::convertIsbn13to10($this->isbn));
    }

    #[Override]
    public function to13() : Isbn13
    {
        return $this;
    }
}
