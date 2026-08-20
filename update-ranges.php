<?php

declare(strict_types=1);

use Brick\VarExporter\VarExporter;

chdir(__DIR__);

require 'vendor/autoload.php';

/*
 * This script downloads and converts the ISBN range file from isbn-international.org.
 *
 * @see https://www.isbn-international.org/range_file_generation
 *
 * When updates are made and an output dir is provided, it writes two files:
 *
 * - commit-message.txt
 * - release-notes.md
 *
 * Usage:
 *
 *     php update-ranges.php
 *     php update-ranges.php --output-dir <dir>
 */

// The values below end up in commit messages and release notes, where GitHub interprets
// issue references, @mentions and Markdown; only accept the expected formats and fail loudly
// if an unexpected value is found.

// UUID, e.g. "51202e58-dc44-4f5a-981b-a5b91c5f0cdf".
const SERIAL_NUMBER_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

// RFC 2822 style date, e.g. "Thu, 20 Aug 2026 00:07:40 BST".
const DATE_PATTERN =
    '/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun), \d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) '
    . '\d{4} \d{2}:\d{2}:\d{2} ([A-Z]{1,5}|[+-]\d{4})$/';

// Agency name, e.g. "Curaçao" or "United States".
const AGENCY_PATTERN = '/^[\p{L}\p{N} ,.\'()-]{1,255}$/u';

function count_valid_isbns(array $rangeData): int
{
    $validIsbnCount = 0;

    foreach ($rangeData as [$prefix1, $prefix2, $name, $ranges]) {
        foreach ($ranges as [$rangeLength, $rangeStart, $rangeEnd]) {
            $totalLength = strlen($prefix1) + strlen($prefix2) + $rangeLength;
            $rangeCount = (int) $rangeEnd - (int) $rangeStart + 1;
            $validIsbnCount += (10 ** (12 - $totalLength)) * $rangeCount;
        }
    }

    return $validIsbnCount;
}

function write_file(string $path, string $contents): void
{
    if (file_put_contents($path, $contents) !== strlen($contents)) {
        fwrite(STDERR, "Could not write file: $path\n");
        exit(1);
    }
}

$rangeMessageXML = file_get_contents('https://www.isbn-international.org/export_rangemessage.xml');

if ($rangeMessageXML === false) {
    fwrite(STDERR, "Could not download range file.\n");
    exit(1);
}

$rangesFile = 'data/ranges.php';
$statsFile = 'data/stats.php';

$document = new DOMDocument();

if (!$document->loadXML($rangeMessageXML)) {
    fwrite(STDERR, "Could not parse range file XML.\n");
    exit(1);
}

$xpath = new DOMXPath($document);

$groupNodeList = $xpath->query('/ISBNRangeMessage/RegistrationGroups/Group');

$rangeCount = 0;
$groupCount = 0;

$rangeData = [];

$messageSerialNumber = $xpath->query('/ISBNRangeMessage/MessageSerialNumber')->item(0)->textContent;

$messageDate = $xpath->query('/ISBNRangeMessage/MessageDate')->item(0)->textContent;

if (preg_match(SERIAL_NUMBER_PATTERN, $messageSerialNumber) !== 1) {
    fwrite(STDERR, 'Invalid message serial number: ' . json_encode($messageSerialNumber) . "\n");
    exit(1);
}

if (preg_match(DATE_PATTERN, $messageDate) !== 1) {
    fwrite(STDERR, 'Invalid message date: ' . json_encode($messageDate) . "\n");
    exit(1);
}

foreach ($groupNodeList as $groupNode) {
    $prefix = $xpath->query('./Prefix', $groupNode)->item(0)->textContent;
    $agency = trim($xpath->query('./Agency', $groupNode)->item(0)->textContent);

    if (preg_match(AGENCY_PATTERN, $agency) !== 1) {
        fwrite(STDERR, 'Invalid agency name: ' . json_encode($agency) . "\n");
        exit(1);
    }

    $ruleNodeList = $xpath->query('./Rules/Rule', $groupNode);

    $ranges = [];

    foreach ($ruleNodeList as $ruleNode) {
        $range = $xpath->query('./Range', $ruleNode)->item(0)->textContent;
        $length = (int) $xpath->query('./Length', $ruleNode)->item(0)->textContent;

        if ($length === 0) {
            // zero indicates range not defined for use.
            continue;
        }

        [$start, $end] = explode('-', $range);

        $start = substr($start, 0, $length);
        $end = substr($end, 0, $length);

        $ranges[] = [$length, $start, $end];
        $rangeCount++;
    }

    $prefix = explode('-', $prefix);

    $rangeData[] = [$prefix[0], $prefix[1], $agency, $ranges];
    $groupCount++;
}

$oldRangeData = require $rangesFile;

if ($oldRangeData === $rangeData) {
    echo "Range file is current.\n";
    exit();
}

$stats = [
    'groupCount' => $groupCount,
    'validIsbnCount' => count_valid_isbns($rangeData),
];

write_file($rangesFile, sprintf("<?php return %s;\n", VarExporter::export(
    $rangeData,
    VarExporter::INLINE_SCALAR_LIST,
)));

write_file($statsFile, sprintf("<?php return %s;\n", VarExporter::export($stats, VarExporter::INLINE_SCALAR_LIST)));

$agenciesUpdated = [];

foreach ($rangeData as [$prefix, $id, $agency, $ranges]) {
    $found = false;
    $identical = false;

    foreach ($oldRangeData as [$oldPrefix, $oldId, $oldAgency, $oldRanges]) {
        if ($oldPrefix === $prefix && $oldId === $id) {
            $found = true;

            if ($oldAgency === $agency && $oldRanges === $ranges) {
                $identical = true;
            }

            break;
        }
    }

    if (!$found || $found && !$identical) {
        $agenciesUpdated[] = $agency;
    }
}

$agenciesUpdated = array_unique($agenciesUpdated);

$commitMessage = "Update ISBN ranges\n";
$commitMessage .= "\n";
$commitMessage .= "Serial number: $messageSerialNumber\n";
$commitMessage .= "Date: $messageDate\n";

if ($agenciesUpdated) {
    $commitMessage .= "\n";
    $commitMessage .= 'Agencies updated: ' . implode(', ', $agenciesUpdated) . "\n";
}

$releaseNotes = "ISBN range update.\n";
$releaseNotes .= "\n";
$releaseNotes .= "| Serial number | Date |\n";
$releaseNotes .= "| ------------- | ---- |\n";
$releaseNotes .= "| $messageSerialNumber | $messageDate |\n";

if ($agenciesUpdated) {
    $releaseNotes .= "\n";
    $releaseNotes .= "Agencies updated:\n";
    $releaseNotes .= "\n";

    foreach ($agenciesUpdated as $agencyUpdated) {
        $releaseNotes .= "- $agencyUpdated\n";
    }
}

echo "Successfully converted $groupCount groups and $rangeCount ranges.\n";
echo "\n";
echo "Commit message:\n";
echo "===============\n";
echo "\n";
echo $commitMessage;

echo "\n";
echo "Release notes:\n";
echo "==============\n";
echo "\n";
echo $releaseNotes;

$options = getopt('', ['output-dir:']);
$outputDir = $options['output-dir'] ?? null;

if (is_string($outputDir) && $outputDir !== '') {
    write_file($outputDir . '/commit-message.txt', $commitMessage);
    write_file($outputDir . '/release-notes.md', $releaseNotes);
}
