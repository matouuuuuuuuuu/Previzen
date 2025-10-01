<?php
$q = strtolower($_GET['q'] ?? '');
$dep = $_GET['dep'] ?? null;
$matches = [];

if (($handle = fopen('./data/communes.csv', 'r')) !== false) {
    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        if (count($row) < 3) continue;

        $nomVille = $row[1];
        $codeDep = $row[2];

        if ($dep && $codeDep !== $dep) continue;
        if (stripos($nomVille, $q) === 0) {
            $matches[] = $nomVille;
            if (count($matches) >= 10) break;
        }
    }
    fclose($handle);
}

header('Content-Type: application/json');
echo json_encode(array_values(array_unique($matches)));
