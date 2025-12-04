<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

use Override;

final readonly class Isbn10 extends Isbn
{
    #[Override]
    public static function of(string $isbn) : Isbn10
    {
        return parent::of($isbn)->to10();
    }

    #[Override]
    public function is10() : bool
    {
        return true;
    }

    #[Override]
    public function is13() : bool
    {
        return false;
    }

    #[Override]
    public function isConvertibleTo10() : bool
    {
        return true;
    }

    #[Override]
    public function to10() : Isbn10
    {
        return $this;
    }

    #[Override]
    public function to13() : Isbn13
    {
        return self::newIsbn13(Internal\Converter::convertIsbn10to13($this->isbn));
    }
}
