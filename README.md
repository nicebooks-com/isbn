# Nicebooks ISBN library

This library provides the functionality to validate, format and convert ISBN numbers, that powers the [nicebooks.com](https://nicebooks.com) website.
It is released under a permissive MIT open-source license for anyone to use.

ISBN formatting follows the rules defined by the [ISBN range file](https://www.isbn-international.org/range_file_generation) published by ISBN International.

[![Build Status](https://travis-ci.org/nicebooks-com/isbn.svg?branch=master)](https://travis-ci.org/nicebooks-com/isbn)
[![Coverage Status](https://img.shields.io/coveralls/nicebooks-com/isbn.svg)](https://coveralls.io/r/nicebooks-com/isbn?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nicebooks/isbn/v/stable)](https://packagist.org/packages/nicebooks/isbn)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

## Installation

This library is installable via [Composer](https://getcomposer.org/).
Just define the following requirement in your `composer.json` file:

    {
        "require": {
            "nicebooks/isbn": "0.1.*"
        }
    }

## Requirements

This library requires PHP 5.4 or higher. [HHVM](http://hhvm.com/) is officially supported.

## Project status & release process

While this library is still under development, it is well tested and should be stable enough to use in production
environments. It is currently in use in production on [nicebooks.com](https://nicebooks.com/).

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing
existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.1.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/nicebooks-com/isbn/releases)
for a list of changes introduced by each further `0.x.0` version.

## Overview

### The `IsbnTools` class

This class contains all the tools to work with ISBN numbers as plain strings.

    use Nicebooks\Isbn\IsbnTools;
    $tools = new IsbnTools();

Its constructor offers two configurable parameters:

    public function __construct($cleanupBeforeValidate = true, $validateCheckDigit = true)

- `$cleanupBeforeValidate` removes any non-alphanumeric ASCII character from the string before validating it.
- `$validateCheckDigit` computes the checksum of the ISBN number before any operation.

Method summary:

- `isValidIsbn(string $isbn) : bool` checks that the given ISBN is valid.

    ```
    var_export($tools->isValidIsbn('123456789X')); // true
    var_export($tools->isValidIsbn('9781234567897')); // true
    ```

- `isValidIsbn10(string $isbn) : bool` checks that the given ISBN is a valid ISBN-10.

    ```
    var_export($tools->isValidIsbn10('123456789X')); // true
    ```

- `isValidIsbn13(string $isbn) : bool` checks that the given ISBN is a valid ISBN-13.

    ```
    var_export($tools->isValidIsbn13('9781234567897')); // true
    ```

- `convertIsbn10to13(string $isbn) : string` converts the given ISBN-10 to an ISBN-13.

    ```
    var_export($tools->convertIsbn10to13('123456789X')); // '9781234567897'
    ```

- `convertIsbn13to10(string $isbn) : string` converts the given ISBN-13 to an ISBN-10.

    ```
    var_export($tools->convertIsbn10to13('9781234567897')); // '123456789X'
    ```

- `format(string $isbn) : string` formats the given ISBN by adding hyphens at the proper places.

    ```
    var_export($formatter->format('123456789X')); // '1-234-56789-X'
    var_export($formatter->format('9781234567897')); // '978-1-234-56789-7'
    ```

### The `Isbn` class

The `Isbn` class is an immutable class representing a valid ISBN-10 or ISBN-13.
It is an alternate way to access the functionality provided by `IsbnTools`, and offers a convenient way to pass an ISBN number around,
guaranteeing both its validity and its integrity.

An `Isbn` instance is obtained with the `of()` factory method:

    use Nicebooks\Isbn\Isbn;
    $isbn = Isbn::of('123456789X');

Method summary:

- `is10() : bool` Returns `true` for an ISBN-10, or `false` for an ISBN-13.
- `is13() : bool` Returns `true` for an ISBN-13, or `false` for an ISBN-10.
- `isConvertibleTo10() : bool` Returns `true` if the ISBN can be converted to an ISBN-10, `false` otherwise.
- `to10() : Isbn` Returns an `Isbn` instance representing the ISBN converted to an ISBN-10.
- `to13() : Isbn` Returns an `Isbn` instance representing the ISBN converted to an ISBN-13.
- `format() : string` Returns the formatted representation of the ISBN.

### Exceptions

Exceptions live in the `Nicebooks\Isbn\Exception` namespace.

- `InvalidIsbnException` is thrown when an invalid ISBN is detected
- `IsbnNotConvertibleException` is thrown when trying to convert an ISBN-13 that does not start with '978' to an ISBN-10.
