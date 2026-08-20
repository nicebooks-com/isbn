<?php

declare(strict_types=1);

/*
 * This script reads git tags on stdin, one per line, finds the highest X.Y.Z
 * version, and prints the next patch version.
 *
 * Usage:
 *
 *     git tag --list | php next-version.php
 */

$versions = [];

foreach (explode("\n", stream_get_contents(STDIN)) as $line) {
    if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $line) === 1) {
        $versions[] = $line;
    }
}

if (!$versions) {
    fwrite(STDERR, "No version tag found on standard input.\n");
    exit(1);
}

usort($versions, version_compare(...));

$latest = end($versions);

[$major, $minor, $patch] = explode('.', $latest);

printf("%s.%s.%d\n", $major, $minor, (int) $patch + 1);
