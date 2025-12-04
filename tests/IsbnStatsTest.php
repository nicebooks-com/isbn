<?php

declare(strict_types=1);

namespace Nicebooks\Isbn\Tests;

use Nicebooks\Isbn\IsbnStats;
use PHPUnit\Framework\TestCase;

class IsbnStatsTest extends TestCase
{
    public function testGetGroupCount() : void
    {
        $groupCount = IsbnStats::getGroupCount();

        $this->assertIsInt($groupCount);
        $this->assertGreaterThan(200, $groupCount);
        $this->assertLessThan(400, $groupCount);
    }

    public function testGetValidIsbnCount() : void
    {
        $validIsbnCount = IsbnStats::getValidIsbnCount();

        $this->assertIsInt($validIsbnCount);
        $this->assertGreaterThan(1_000_000_000, $validIsbnCount);
        $this->assertLessThan(2_000_000_000, $validIsbnCount);
    }
}
