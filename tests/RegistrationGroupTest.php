<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\RegistrationGroup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for class RegistrationGroupTest.
 */
class RegistrationGroupTest extends TestCase
{
    public function testReturnTypesAndCounts(): void
    {
        $isbn13Groups = RegistrationGroup::all();

        self::assertIsArray($isbn13Groups);

        self::assertGreaterThan(0, count($isbn13Groups));

        foreach ($isbn13Groups as $isbnGroup) {
            self::assertInstanceOf(RegistrationGroup::class, $isbnGroup);
        }
    }

    #[Depends('testReturnTypesAndCounts')]
    #[DataProvider('providerIsbnRegistrationGroupContents')]
    public function testIsbnRegistrationGroupContents(string $prefix, string $identifier, string $name): void
    {
        $isbnGroups = RegistrationGroup::all();

        foreach ($isbnGroups as $isbnGroup) {
            if ($isbnGroup->prefix === $prefix && $isbnGroup->identifier === $identifier) {
                self::assertSame($name, $isbnGroup->name);

                return;
            }
        }

        self::fail(sprintf(
            'Failed to assert that ISBN registration groups contain (%s, %s, %s)',
            $prefix,
            $identifier,
            $name,
        ));
    }

    public static function providerIsbnRegistrationGroupContents(): array
    {
        return [
            ['978', '0',     'English language'],
            ['978', '1',     'English language'],
            ['978', '2',     'French language'],
            ['978', '3',     'German language'],
            ['978', '4',     'Japan'],
            ['978', '611',   'Thailand'],
            ['978', '85',    'Brazil'],
            ['978', '88',    'Italy'],
            ['978', '99970', 'Haiti'],
            ['979', '12',    'Italy'],
        ];
    }
}
