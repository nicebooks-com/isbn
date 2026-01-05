# Nicebooks ISBN library for PHP

This library provides functionality for validating, formatting, and converting ISBN numbers. It powers the [nicebooks.com](https://nicebooks.com) website and is available for use under the permissive MIT open-source license.

ISBN validation and formatting follow the rules defined by the [ISBN range file](https://www.isbn-international.org/range_file_generation) published by the International ISBN Agency.

[![Build Status](https://github.com/nicebooks-com/isbn/workflows/CI/badge.svg)](https://github.com/nicebooks-com/isbn/actions)
[![Coverage Status](https://coveralls.io/repos/github/nicebooks-com/isbn/badge.svg?branch=master)](https://coveralls.io/github/nicebooks-com/isbn?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nicebooks/isbn/v/stable)](https://packagist.org/packages/nicebooks/isbn)
[![Total Downloads](https://poser.pugx.org/nicebooks/isbn/downloads)](https://packagist.org/packages/nicebooks/isbn)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require nicebooks/isbn
```

## Requirements

The current version of this library requires PHP 8.2 or higher.

You may use earlier versions of `nicebooks/isbn` with earlier versions of PHP, but you will only receive ISBN range updates if you use the latest version.

## Project status & release process

While this library is still under development, it is well tested and should be stable enough to use in production
environments. It is currently in use in production on [nicebooks.com](https://nicebooks.com/).

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (updating ISBN ranges,
adding new methods, optimizing existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.6.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/nicebooks-com/isbn/releases)
for a list of changes introduced by each further `0.x.0` version.

## Overview

### The `Isbn` class

The `Isbn` class is an abstract, immutable class representing an ISBN-10 or ISBN-13 with a valid format and a valid check digit.

It has 2 subclasses: `Isbn10` and `Isbn13`, allowing for narrower typing if your application expects only ISBN-10 or only ISBN-13 at some point.

An `Isbn` instance is obtained with the `of()` factory method:

```php
use Nicebooks\Isbn\Isbn;

$isbn = Isbn::of('123456789X'); // will return an instance of Isbn10
$isbn = Isbn::of('9781234567897'); // will return an instance of Isbn13
```

You can also use the `Isbn10::of()` and `Isbn13::of()` factory methods, which will attempt to convert the given ISBN to the corresponding subclass:

```php
Isbn10::of('123456789X'); // equivalent to Isbn::of('123456789X')->to10();
Isbn13::of('9781234567897'); // equivalent to Isbn::of('9781234567897')->to13();
```

If the given string does not have a valid format or check digit, an `InvalidIsbnException` is thrown.

> [!NOTE]
> Before validation, the provided string is stripped of all non-alphanumeric ASCII characters.

### Checking the type of an ISBN

You can check the type of an ISBN with the `is10()` and `is13()` methods:

```php
Isbn::of('123456789X')->is10(); // true
Isbn::of('123456789X')->is13(); // false

Isbn::of('9781234567897')->is10(); // false
Isbn::of('9781234567897')->is13(); // true
```

You can also use `instanceof` checks:

```php
Isbn::of('123456789X') instanceof Isbn10; // true
Isbn::of('9781234567897') instanceof Isbn13; // true
```

### Converting between ISBN-10 and ISBN-13

An ISBN-10 can be converted to an ISBN-13 with the `to13()` method:

```php
Isbn::of('123456789X')->to13(); // Isbn13 instance with value 9781234567897
```

An ISBN-13 can be converted to an ISBN-10 with the `to10()` method:

```php
Isbn::of('9781234567897')->to10(); // Isbn10 instance with value 123456789X
```

Only ISBN-13 numbers starting with `978` can be converted to ISBN-10. You can check if an ISBN can be converted to an ISBN-10 with the `isConvertibleTo10()` method:

```php
Isbn::of('9781234567897')->isConvertibleTo10(); // true
Isbn::of('9791234567896')->isConvertibleTo10(); // false
```

If you call `to10()` on an ISBN-13 that does not start with `978`, an `IsbnNotConvertibleException` is thrown.

### Comparing ISBN numbers

You can compare two `Isbn` instances with the `isEqualTo()` method. An ISBN-10 can be compared to an ISBN-13, and the comparison will be successful if both numbers resolve to the same ISBN-13 number:

```php
Isbn::of('123456789X')->isEqualTo(Isbn::of('9781234567897')); // true
```

### Validating an ISBN

While an `Isbn` instance is guaranteed to have a valid format and a valid check digit, it may not belong to a valid range according to the range file published by the International ISBN Agency.

The `Isbn` class provides two methods to further validate an ISBN:

| Method                        | Description                                                                                                                                                                              |
|-------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `hasValidRegistrationGroup()` | Only checks if the ISBN belongs to a **known registration group**.                                                                                                                       |
| `isValid()`                   | Checks if the ISBN belongs to a **known registration group** and a **known range**.<br>This is the highest level of validation that can be performed by looking at the ISBN number alone. |

> [!NOTE]
> Which ISBNs are considered valid depends on the library version you are using. This library is kept in sync with the ISBN ranges published by the International ISBN Agency, so keep it up to date for proper validation.


### Formatting an ISBN

The library provides two methods to format an ISBN:

| Method                | Description                                                                               | ISBN-10 example | ISBN-13 example     |
|-----------------------|-------------------------------------------------------------------------------------------|-----------------|---------------------|
| `toString()`          | Returns the unformatted ISBN                                                              | `133887893X`    | `9781338878936`     |
| `toFormattedString()` | Returns the formatted ISBN if the ISBN is valid, otherwise returns the unformatted number | `1-338-87893-X` | `978-1-338-87893-6` |

### Splitting an ISBN into parts

An ISBN-13 is divided into 5 parts:

```
978-1-338-87893-6
——— — ——— ————— —
 A  B  C    D   E
```

While an ISBN-10 is divided into 4 parts:

```
1-338-87893-X
— ——— ————— —
B  C    D   E
```

The `Isbn` class provides getters for each part:

| Part | Example | Description                       | Getter                               |
|------|---------|-----------------------------------|--------------------------------------|
| `A`  | `978`   | Prefix                            | `getRegistrationGroup()->prefix`     |
| `B`  | `1`     | Registration group identifier     | `getRegistrationGroup()->identifier` |
| `C`  | `338`   | Publisher identifier (registrant) | `getPublisherIdentifier()`           |
| `D`  | `87893` | Title identifier (publication)    | `getTitleIdentifier()`               |
| `E`  | `6`     | Check digit                       | `getCheckDigit()`                    |

> [!IMPORTANT]
> - Parts `A` and `B` require `hasValidRegistrationGroup()` (or `isValid()`, which implies it)
> - Parts `C` and `D` require `isValid()`

You can also get the ISBN parts as an array:

```php
Isbn::of('9781338878936')->getParts(); // ['978', '1', '338', '87893', '6']
```

> [!IMPORTANT]
> `getParts()` requires `isValid()`

If the part you're trying to access is not available because the ISBN does not belong to a valid registration group or range, an `IsbnException` is thrown.

#### Getting the registration group name

`getRegistrationGroup()` also exposes the group's `name`:

```php
Isbn::of('9781338878936')->getRegistrationGroup()->name; // 'English language'
```

### Exceptions

Exceptions live in the `Nicebooks\Isbn\Exception` namespace.

- `IsbnException` is the base class for all exceptions thrown by this library.
  - `InvalidIsbnException` is thrown when an invalid ISBN is detected
  - `IsbnNotConvertibleException` is thrown when trying to convert an ISBN-13 that does not start with `978` to an ISBN-10.
