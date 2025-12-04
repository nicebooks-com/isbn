<?php

declare(strict_types=1);

namespace Nicebooks\Isbn;

/**
 * @psalm-type StatsType = array{groupCount: int, validIsbnCount: int}
 */
final class IsbnStats
{
    /**
     * @psalm-var StatsType|null
     */
    private static ?array $stats = null;

    /**
     * @psalm-return StatsType
     */
    private static function getStats() : array
    {
        if (self::$stats === null) {
            self::$stats = require __DIR__ . '/../data/stats.php';
        }

        return self::$stats;
    }

    public static function getGroupCount() : int
    {
        return self::getStats()['groupCount'];
    }

    public static function getValidIsbnCount() : int
    {
        return self::getStats()['validIsbnCount'];
    }
}
