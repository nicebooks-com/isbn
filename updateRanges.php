<?php

declare(strict_types=1);

use Brick\VarExporter\VarExporter;

chdir(__DIR__);

require 'vendor/autoload.php';

/*
 * This script downloads and converts the ISBN range file from isbn-international.org.
 *
 * @see https://www.isbn-international.org/range_file_generation
 */

$rangeMessageXML = file_get_contents('https://www.isbn-international.org/export_rangemessage.xml');

if ($rangeMessageXML === false) {
    echo "Could not download range file.\n";
    exit;
}

$rangeFile = 'data/ranges.php';

$document = new DOMDocument();
$document->loadXML($rangeMessageXML);

$xpath = new DOMXPath($document);

$groupNodeList = $xpath->query('/ISBNRangeMessage/RegistrationGroups/Group');

$rangeCount = 0;
$groupCount = 0;

$rangeData = [];

$messageSerialNumber = $xpath->query('/ISBNRangeMessage/MessageSerialNumber')->item(0)->textContent;

$messageDate = $xpath->query('/ISBNRangeMessage/MessageDate')->item(0)->textContent;
$messageTime = strtotime($messageDate);

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

        [$start, $end] = explode('-', $range);

        $start = substr($start, 0, $length);
        $end = substr($end, 0, $length);

        $ranges[] = [$length, $start, $end];
        $rangeCount++;
    }

    $prefix = explode('-', $prefix);

    $rangeData[] = [$prefix[0], $prefix[1], trim($agency), $ranges];
    $groupCount++;
}

$oldRangeData = require $rangeFile;

if ($oldRangeData === $rangeData) {
    echo "Range file is current.\n";
    exit;
}

file_put_contents($rangeFile, sprintf(
    "<?php return %s;\n",
    VarExporter::export($rangeData, VarExporter::INLINE_SCALAR_LIST)
));

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

    if ((! $found) || ($found && ! $identical)) {
        $agenciesUpdated[] = $agency;
    }
}

$agenciesUpdated = array_unique($agenciesUpdated);

$commitMessage  = "Update ISBN ranges\n";
$commitMessage .= "\n";
$commitMessage .= "Serial number: $messageSerialNumber\n";
$commitMessage .= "Date: $messageDate\n";

if ($agenciesUpdated) {
    $commitMessage .= "\n";
    $commitMessage .= "Agencies updated: " . implode(', ', $agenciesUpdated) . "\n";
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
echo "ISBN range update.\n";
echo "\n";
echo "| Serial number | Date |\n";
echo "| ------------- | ---- |\n";
echo "| $messageSerialNumber | $messageDate |\n";
echo "\n";

if ($agenciesUpdated) {
    echo "\n";
    echo "Agencies updated:\n";
    echo "\n";

    foreach ($agenciesUpdated as $agencyUpdated) {
        echo "- $agencyUpdated\n";
    }
}

echo "\n";

system('vendor/bin/phpunit --colors=always', $status);

if ($status === 0) {
    system('git commit -a -m ' . escapeshellarg($commitMessage));
    system('git push');
}
