<?php
header('Content-Type: application/json');
$csvFile = 'stats.csv';

$cityCounts = [];
$dateCounts = [];

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    fgetcsv($handle); // skip header
    while (($data = fgetcsv($handle)) !== FALSE) {
        $city = $data[0];
        $date = $data[1];

        if (!isset($cityCounts[$city])) $cityCounts[$city] = 0;
        $cityCounts[$city]++;

        if (!isset($dateCounts[$date])) $dateCounts[$date] = 0;
        $dateCounts[$date]++;
    }
    fclose($handle);
}

echo json_encode([
    "cities" => $cityCounts,
    "daily" => $dateCounts
]);
