# Nicebooks ISBN library

This library provides the functionality to validate, format and convert ISBN numbers, that powers the [nicebooks.com](http://nicebooks.com) website.
It is released under a permissive MIT open-source license for anyone to use.

## The `IsbnTools` class

This class contains all the tools to work with ISBN numbers as plain strings.

Its constructor offers two configurable parameters:

    public function __construct($cleanupBeforeValidate = true, $validateCheckDigit = true)

- `$cleanupBeforeValidate` removes any non-alphanumeric ASCII character from the string before validating it.
- `$validateCheckDigit` computes the checksum of the ISBN number before any operation.

Method summary:

    $tools = new IsbnTools();

- `isValidIsbn(string $isbn) : boolean` checks that the given ISBN is valid.

    ```
    var_export($tools->isValidIsbn('123456789X')); // true
    var_export($tools->isValidIsbn('9781234567897')); // true
    ```

- `isValidIsbn10(string $isbn) : boolean` checks that the given ISBN is a valid ISBN-10.

    ```
    var_export($tools->isValidIsbn10('123456789X')); // true
    ```

- `isValidIsbn13(string $isbn) : boolean` checks that the given ISBN is a valid ISBN-13.

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

## The `Isbn` class

The `Isbn` class is an immutable class representing a valid ISBN-10 or ISBN-13.
It is an alternate way to access the functionality provided by `IsbnTools`, and offers a convenient way to pass an ISBN number around,
guaranteing both its validity and its integrity.

An `Isbn` instance is obtained with the `get()` factory method:

    $isbn = Isbn::get('123456789X');

Method summary:

- `is10() : boolean` Returns `true` for an ISBN-10, or `false` for an ISBN-13.
- `is13() : boolean` Returns `true` for an ISBN-13, or `false` for an ISBN-10.
- `isConvertibleTo10() : boolean` Returns `true` if the ISBN can be converted to an ISBN-10, `false` otherwise.
- `to10() : Isbn` Returns an `Isbn` instance representing the ISBN converted to an ISBN-10.
- `to13() : Isbn` Returns an `Isbn` instance representing the ISBN converted to an ISBN-13.
- `format() : string` Returns the formatted representation of the ISBN.

## Exceptions

- `Nicebooks\Isbn\Exception\InvalidIsbnException` is thrown when an invalid ISBN is detected
- `Nicebooks\Isbn\Exception\IsbnNotConvertibleException` is thrown when trying to convert an ISBN-13 that does not start with '978' to an ISBN-10.

## Formatting

ISBN formatting follows the rules defined by the [ISBN range file](https://www.isbn-international.org/range_file_generation) published by ISBN International.
