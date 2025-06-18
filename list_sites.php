<?php
/**
 * Helper script to list all available sites and their IP addresses
 * Usage: php list_sites.php [search_term]
 */

$sites_file = __DIR__ . '/sites.json';

if (!file_exists($sites_file)) {
    echo "Error: sites.json file not found at $sites_file\n";
    exit(1);
}

$data = file_get_contents($sites_file);
$sites = json_decode($data, true);

if ($sites === null) {
    echo "Error: Failed to parse sites.json\n";
    exit(1);
}

// Get optional search term
$search_term = isset($argv[1]) ? strtolower($argv[1]) : null;

echo "\033[94mAvailable Sites:\033[0m\n";
echo str_repeat("=", 60) . "\n";
printf("%-25s %-15s\n", "Site Name", "IP Address");
echo str_repeat("-", 60) . "\n";

$count = 0;
foreach ($sites as $site) {
    $name = $site['name'];
    $ip = $site['ip'];
    
    // If search term is provided, filter results
    if ($search_term !== null) {
        if (strpos(strtolower($name), $search_term) === false && 
            strpos(strtolower($ip), $search_term) === false) {
            continue;
        }
    }
    
    printf("%-25s %-15s\n", $name, $ip);
    $count++;
}

echo str_repeat("-", 60) . "\n";
echo "Total sites" . ($search_term ? " (filtered)" : "") . ": $count\n";

if ($search_term !== null && $count === 0) {
    echo "\nNo sites found matching '$search_term'\n";
}

echo "\n\033[92mUsage Examples:\033[0m\n";
echo "  Run for all sites:     php script.php your_query.sql\n";
echo "  Run for specific site: php script.php your_query.sql 10.202.30.5\n";
echo "  Search sites:          php list_sites.php karen\n";
?>

