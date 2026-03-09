<?php
$file = 'd:\1. Clientes\50. DesbravaHub\views\admin\users.php';
if (!file_exists($file)) die("File not found");

$content = file_get_contents($file);

echo "--- HTML Structure Diagnostic ---\n";

// Count <thead> and <tbody>
echo "<thead> count: " . preg_match_all('/<thead>/i', $content) . "\n";
echo "<tbody> count: " . preg_match_all('/<tbody>/i', $content) . "\n";

// Get content inside <thead>
if (preg_match('/<thead>(.*?)<\/thead>/is', $content, $m)) {
    $thead = $m[1];
    $th_count = preg_match_all('/<th\b/i', $thead);
    echo "<th> tags in <thead>: $th_count\n";
    // List TH tags
    preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $thead, $th_matches);
    foreach ($th_matches[1] as $index => $label) {
        echo "  TH[$index]: " . trim(strip_tags($label)) . "\n";
    }
} else {
    echo "Could not find <thead> content\n";
}

// Get content inside <tbody>
if (preg_match('/<tbody>(.*?)<\/tbody>/is', $content, $m)) {
    $tbody = $m[1];
    // Find the first <tr>
    if (preg_match('/<tr>(.*?)<\/tr>/is', $tbody, $row_m)) {
        $first_row = $row_m[1];
        $td_count = preg_match_all('/<td\b/i', $first_row);
        echo "<td> tags in first <tbody> row: $td_count\n";
        // List TD labels
        preg_match_all('/<td[^>]*data-label="([^"]*)"/i', $first_row, $td_matches);
        foreach ($td_matches[1] as $index => $label) {
            echo "  TD[$index] label: $label\n";
        }
        
        // Check for any TD BEFORE the first labeled one
        if (preg_match('/^\s*<td\b/i', trim($first_row))) {
            echo "First element in row is a <td\n";
        }
        
        // Find ALL TD tags in the row
        preg_match_all('/<td\b[^>]*>/i', $first_row, $all_tds);
        echo "Total <td tags in first row: " . count($all_tds[0]) . "\n";
    } else {
        echo "Could not find any <tr> inside <tbody>\n";
    }
} else {
    echo "Could not find <tbody> content\n";
}

echo "--- End Diagnostic ---\n";
