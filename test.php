<?php
$ruleSequence = ['1' => 'parcom_ring_synchronizer', '2' => 'astra_engine', '3' => 'parcom_shaft_gc'];
$processName = 'parcom_shaft_gc';
$position = null;
foreach ($ruleSequence as $key => $process) {
    if ($process === $processName) {
        $position = (int) $key;
        break;
    }
}
$record = []; // empty record
$missingPrevious = [];
for ($i = 1; $i < $position; $i++) {
    // Array access using integer $i instead of string "$i"
    $prevProcess = $ruleSequence[$i] ?? null;
    // Wait, in JSON decoded associative array, keys might be strings!
    if ($prevProcess === null && isset($ruleSequence[(string)$i])) {
        $prevProcess = $ruleSequence[(string)$i];
    }
    if ($prevProcess && !isset($record[$prevProcess])) {
        $missingPrevious[] = $prevProcess;
    }
}
print_r($missingPrevious);
