<?php

/*
 * This script converts a RangeMessage.xml file into a PHP data file.
 *
 * The RangeMessage.xml file can be downloaded from:
 * https://www.isbn-international.org/range_file_generation
 */

$document = new DOMDocument();
$document->load('RangeMessage.xml');

$xpath = new DOMXPath($document);

$groupNodeList = $xpath->query('/ISBNRangeMessage/RegistrationGroups/Group');

$rangeCount = 0;
$groupCount = 0;

$rangeData = array();

foreach ($groupNodeList as $groupNode) {
    $prefix = $xpath->query('./Prefix', $groupNode)->item(0)->textContent;
    $agency = $xpath->query('./Agency', $groupNode)->item(0)->textContent;

    $ruleNodeList = $xpath->query('./Rules/Rule', $groupNode);

    $ranges = array();

    foreach ($ruleNodeList as $ruleNode) {
        $range = $xpath->query('./Range', $ruleNode)->item(0)->textContent;
        $length = (int) $xpath->query('./Length', $ruleNode)->item(0)->textContent;

        if ($length === 0) {
            // zero indicates range not defined for use.
            continue;
        }

        list ($start, $end) = explode('-', $range);

        $start = substr($start, 0, $length);
        $end = substr($end, 0, $length);

        $ranges[] = array($length, $start, $end);
        $rangeCount++;
    }

    $prefix = explode('-', $prefix);

    $rangeData[] = array($prefix[0], $prefix[1], $agency, $ranges);
    $groupCount++;
}

file_put_contents('data/ranges.php', '<?php return ' . var_export($rangeData,  true) . ';');
printf('Successfully converted %d groups and %d ranges.' . PHP_EOL, $groupCount, $rangeCount);
