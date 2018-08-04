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

$rangeData = [];

foreach ($groupNodeList as $groupNode) {
    $prefix = $xpath->query('./Prefix', $groupNode)->item(0)->textContent;
    $agency = $xpath->query('./Agency', $groupNode)->item(0)->textContent;

    $ruleNodeList = $xpath->query('./Rules/Rule', $groupNode);

    $ranges = [];

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

        $ranges[] = [$length, $start, $end];
        $rangeCount++;
    }

    $prefix = explode('-', $prefix);

    $rangeData[] = [$prefix[0], $prefix[1], $agency, $ranges];
    $groupCount++;
}

/**
 * Compact & pretty alternative to var_export().
 *
 * @param mixed $variable
 * @param int   $indent
 *
 * @return string
 */
function export($variable, int $indent = 0)
{
    if (! is_array($variable)) {
        return var_export($variable, true);
    }

    $isNumeric = (array_values($variable) === $variable);

    $hasArray = false;

    foreach ($variable as $value) {
        if (is_array($value)) {
            $hasArray = true;
            break;
        }
    }

    $spaces = str_repeat("\t", $indent + 1);

    $result = '[';

    if ($hasArray) {
        $result .= "\n";
    }

    $count = count($variable);
    $current = 0;

    foreach ($variable as $key => $value) {
        if ($hasArray) {
            $result .= $spaces;
        }

        if (! $isNumeric) {
            $result .= export($key) . ' => ';
        }

        $result .= export($value, $indent + 1);

        if (++$current !== $count) {
            $result .= ',';
        }

        if ($hasArray) {
            $result .= "\n";
        } elseif ($current !== $count) {
            $result .= ' ';
        }
    }

    if ($hasArray) {
        $result .= str_repeat("\t", $indent);
    }

    $result .= ']';

    return $result;
}

file_put_contents('data/ranges.php', '<?php return ' . export($rangeData) . ";\n");
printf('Successfully converted %d groups and %d ranges.' . PHP_EOL, $groupCount, $rangeCount);
