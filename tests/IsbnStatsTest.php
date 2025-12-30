<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnStats;
use PHPUnit\Framework\TestCase;

class IsbnStatsTest extends TestCase
{
    public function testGetGroupCount(): void
    {
        $groupCount = IsbnStats::getGroupCount();

        self::assertIsInt($groupCount);
        self::assertGreaterThan(200, $groupCount);
        self::assertLessThan(400, $groupCount);
    }

    public function testGetValidIsbnCount(): void
    {
        $validIsbnCount = IsbnStats::getValidIsbnCount();

        self::assertIsInt($validIsbnCount);
        self::assertGreaterThan(1_000_000_000, $validIsbnCount);
        self::assertLessThan(2_000_000_000, $validIsbnCount);
    }
}
