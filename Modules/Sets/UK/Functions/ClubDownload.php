<?php
//Exporter for Club Names and Codes, live from AGB Extranet for people to reference.
session_start();

// Fetch JSON from remote URL
$url = 'https://records.agbextranet.org.uk/Public/ClubNames.php';
$json = file_get_contents($url);
if ($json === false) {
    die("Failed to fetch JSON");
}

// Decode JSON
$data = json_decode($json, true);
if (!is_array($data)) {
    die("Invalid JSON data");
}

// Set headers to force CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="clubs.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Write header row using keys from first record
if (!empty($data)) {
    fputcsv($output, array_keys($data[0]));
}

// Write each row
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
