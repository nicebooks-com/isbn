<?php

declare(strict_types=1);

use Brick\VarExporter\VarExporter;

require __DIR__ . '/vendor/autoload.php';

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

$rangeFile = __DIR__ . '/data/ranges.php';

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

if (file_exists($rangeFile)) {
    $firstLine = fgets(fopen($rangeFile, 'r'));
    $startPos = strpos($firstLine, '/* ') + 3;
    $endPos = strpos($firstLine, ' */');
    $currentMessageDate = substr($firstLine, $startPos, $endPos - $startPos);
    $currentMessageTime = strtotime($currentMessageDate);

    if ($messageTime === $currentMessageTime) {
        echo "Range file is current.\n";
        exit;
    }

    if ($messageTime < $currentMessageTime)  {
        echo "Range file is older than the current file.\n";
        exit;
    }
}

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

file_put_contents($rangeFile, sprintf(
    "<?php return /* %s */ %s;\n",
    $messageDate,
    VarExporter::export($rangeData, VarExporter::INLINE_NUMERIC_SCALAR_ARRAY)
));

echo "Successfully converted $groupCount groups and $rangeCount ranges.\n";
echo "Serial number: $messageSerialNumber\n";
echo "Date: $messageDate\n";
