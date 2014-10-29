# Nicebooks ISBN library

This library provides the ISBN functionality that powers the [nicebooks.com](http://nicebooks.com) website. It is released under a permissive MIT open-source license for anyone to use.

## IsbnFormatter

This class formats an ISBN number by adding hyphens at the proper places as defined by the [ISBN range file](https://www.isbn-international.org/range_file_generation) published by ISBN International:

    $formatter = new Nicebooks\Isbn\IsbnFormatter();
    echo $formatter->format('123456789X'); // 1-234-56789-X
    echo $formatter->format('9781234567897'); // 978-1-234-56789-7
