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

$groups = $xpath->query('/ISBNRangeMessage/RegistrationGroups/Group');

$data = array();

foreach ($groups as $group) {
    $prefix = $xpath->query('./Prefix', $group)->item(0)->textContent;
    $agency = $xpath->query('./Agency', $group)->item(0)->textContent;

    list ($prefix, $registrationGroup) = explode('-', $prefix);

    $rules = $xpath->query('./Rules/Rule', $group);

    foreach ($rules as $rule) {
        $range = $xpath->query('./Range', $rule)->item(0)->textContent;
        $length = (int) $xpath->query('./Length', $rule)->item(0)->textContent;

        if ($length === 0) {
            // zero indicates range not defined for use.
            continue;
        }

        list ($start, $end) = explode('-', $range);

        $start = substr($start, 0, $length);
        $end = substr($end, 0, $length);

        $data[$prefix][$registrationGroup][] = array($length, $start, $end);
    }
}

file_put_contents('data/ranges.php', '<?php return ' . var_export($data, true). ';');
