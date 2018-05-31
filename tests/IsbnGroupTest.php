<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnGroup;

/**
 * Unit tests for class IsbnGroup.
 */
class IsbnGroupTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnTypesAndCounts() : void
    {
        $isbn10Groups = IsbnGroup::getIsbn10Groups();
        $isbn13Groups = IsbnGroup::getIsbn13Groups();

        $this->assertInternalType('array', $isbn10Groups);
        $this->assertInternalType('array', $isbn13Groups);

        $this->assertGreaterThan(0, count($isbn10Groups));
        $this->assertGreaterThan(0, count($isbn13Groups));

        $this->assertGreaterThan(count($isbn10Groups), count($isbn13Groups));

        foreach ($isbn10Groups as $isbnGroup) {
            $this->assertInstanceOf('Nicebooks\Isbn\IsbnGroup', $isbnGroup);
        }
        foreach ($isbn13Groups as $isbnGroup) {
            $this->assertInstanceOf('Nicebooks\Isbn\IsbnGroup', $isbnGroup);
        }
    }

    /**
     * @depends testReturnTypesAndCounts
     * @dataProvider providerIsbnGroupContents
     *
     * @param bool   $is13
     * @param string $prefix
     * @param string $name
     */
    public function testIsbnGroupContents(bool $is13, string $prefix, string $name) : void
    {
        $isbnGroups = $is13
            ? IsbnGroup::getIsbn13Groups()
            : IsbnGroup::getIsbn10Groups();

        foreach ($isbnGroups as $isbnGroup) {
            if ($isbnGroup->getPrefix() === $prefix) {
                if ($isbnGroup->getName() === $name) {
                    $this->addToAssertionCount(1);
                    return;
                }
            }
        }

        $this->fail(sprintf('Failed to assert that ISBN groups contain (%s, %s)', $prefix, $name));

    }

    /**
     * @return array
     */
    public function providerIsbnGroupContents() : array
    {
        return [
            [false, '0', 'English language'],
            [false, '1', 'English language'],
            [false, '2', 'French language'],
            [false, '3', 'German language'],
            [false, '4', 'Japan'],
            [false, '611', 'Thailand'],
            [false, '85', 'Brazil'],
            [false, '88', 'Italy'],
            [false, '99970', 'Haiti'],

            [true, '978-0', 'English language'],
            [true, '978-1', 'English language'],
            [true, '978-2', 'French language'],
            [true, '978-3', 'German language'],
            [true, '978-4', 'Japan'],
            [true, '978-611', 'Thailand'],
            [true, '978-85', 'Brazil'],
            [true, '978-88', 'Italy'],
            [true, '978-99970', 'Haiti'],
            [true, '979-12', 'Italy'],
        ];
    }
}
