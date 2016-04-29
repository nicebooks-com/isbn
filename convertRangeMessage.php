<?php

/*
 * This script converts a RangeMessage.xml file into a PHP data file.
 *
 * The RangeMessage.xml file can be downloaded from:
 * https://www.isbn-international.org/range_file_generation
 */

$document = new DOMDocument();
$document->loadXML(file_get_contents('RangeMessage.xml'));

$xpath = new DOMXPath($document);

$groupNodeList = $xpath->query('/ISBNRangeMessage/RegistrationGroups/Group');

$ranges = array();
$groups = array();

$rangeCount = 0;
$groupCount = 0;

foreach ($groupNodeList as $groupNode) {
    $prefix = $xpath->query('./Prefix', $groupNode)->item(0)->textContent;
    $agency = $xpath->query('./Agency', $groupNode)->item(0)->textContent;

    list ($prefix, $registrationGroup) = explode('-', $prefix);

    $groups[$prefix][$registrationGroup] = $agency;
    $groupCount++;

    $ruleNodeList = $xpath->query('./Rules/Rule', $groupNode);

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

        $ranges[$prefix][$registrationGroup][] = array($length, $start, $end);
        $rangeCount++;
    }
}

file_put_contents('data/ranges.php', '<?php return ' . var_export($ranges, true) . ';');
file_put_contents('data/groups.php', '<?php return ' . var_export($groups, true) . ';');

printf('Successfully converted %d groups and %d ranges.' . PHP_EOL, $groupCount, $rangeCount);
