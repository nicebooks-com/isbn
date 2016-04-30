<?php

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnGroup;

/**
 * Unit tests for class IsbnGroup.
 */
class IsbnGroupTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnTypesAndCounts()
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
    public function testIsbnGroupContents($is13, $prefix, $name)
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
    public function providerIsbnGroupContents()
    {
        return array(
            array(false, '0', 'English language'),
            array(false, '1', 'English language'),
            array(false, '2', 'French language'),
            array(false, '3', 'German language'),
            array(false, '4', 'Japan'),
            array(false, '611', 'Thailand'),
            array(false, '85', 'Brazil'),
            array(false, '88', 'Italy'),
            array(false, '99970', 'Haiti'),

            array(true, '978-0', 'English language'),
            array(true, '978-1', 'English language'),
            array(true, '978-2', 'French language'),
            array(true, '978-3', 'German language'),
            array(true, '978-4', 'Japan'),
            array(true, '978-611', 'Thailand'),
            array(true, '978-85', 'Brazil'),
            array(true, '978-88', 'Italy'),
            array(true, '978-99970', 'Haiti'),
            array(true, '979-12', 'Italy'),
        );
    }
}
