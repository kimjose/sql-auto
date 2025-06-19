<?php
try {
    // Check command line arguments
    if ($argc < 2) {
        echo "Usage: php this_file.php <file_path> [ip_address]\n";
        echo "  file_path: Path to SQL file\n";
        echo "  ip_address: (Optional) Specific IP address to run query for. If not provided, runs for all sites.\n";
        exit(1);
    }

    $outputDir = "/home/joseph/Documents/test_output/";

    // Get the file path from the command line arguments
    $file_path = $argv[1];

    // Get optional IP address parameter
    $target_ip = isset($argv[2]) ? $argv[2] : null;

    // Check if the file exists
    if (!file_exists($file_path)) {
        echo "File not found: $file_path\n";
        exit(1);
    }

    // Read the contents of the file
    $file_contents = file_get_contents($file_path);

    // Display the contents of the file
    echo "Contents of $file_path:\n";
    echo $file_contents;
    echo "\n" . str_repeat("=", 50) . "\n";

    $dbUser = "dwapi";
    $dbPassword = "dwapi";

    // Read sites.json
    $sites_file = __DIR__ . '/sites.json';
    if (!file_exists($sites_file)) {
        echo "Error: sites.json file not found at $sites_file\n";
        exit(1);
    }

    $handle = fopen($sites_file, 'r');
    $data = fread($handle, filesize($sites_file));
    fclose($handle);
    $sites = json_decode($data, true);

    if ($sites === null) {
        echo "Error: Failed to parse sites.json\n";
        exit(1);
    }

    // If IP address is provided, find the specific site
    if ($target_ip !== null) {
        $found_site = null;
        foreach ($sites as $site) {
            if ($site['ip'] === $target_ip) {
                $found_site = $site;
                break;
            }
        }

        if ($found_site === null) {
            echo "\033[31mError:\033[0m IP address '$target_ip' not found in sites.json\n";
            echo "Available IPs:\n";
            foreach ($sites as $site) {
                echo "  - {$site['ip']} ({$site['name']})\n";
            }
            exit(1);
        }

        // Run query for specific site
        $ip = $found_site['ip'];
        $name = $found_site['name'];
        $file = $outputDir . $name;

        echo "\033[94mRunning query for specific site:\033[0m\n";
        echo "  Site: $name\n";
        echo "  IP: $ip\n";
        echo "  Output file: $file.csv\n";
        echo str_repeat("-", 50) . "\n";

        echo "\033[93mRunning query for $name ................: \033[0m\n";
        $configFile = __DIR__ . '/.my.cnf';
        $sqlCommand = "mysql --defaults-file=$configFile -h $ip openmrs -e \"" . $file_contents . "\" 2>&1";

        $return_code = 0;
        $output = [];
        exec($sqlCommand, $output, $return_code);

        // Check if output contains 'error' (case-insensitive)
        $output_text = implode("\n", $output);
        $has_error = stripos($output_text, 'error') !== false;

        if ($has_error) {
            echo "\033[31mError:\033[0m Query failed for $name.\n";
            $fromError = substr(
                $output_text,
                (strpos($output_text, "error") !== false ? strpos($output_text, "error") : strpos($output_text, "ERROR")),
                900
            );
            echo "Error output: $fromError\n";
        } else {
            echo "\033[32mSuccess:\033[0m Query executed successfully for $name.\n";
            // Convert tabs to commas and save to CSV file
            $csv_content = str_replace("\t", ",", $output_text);
            file_put_contents("$file.csv", $csv_content);

            if (file_exists("$file.csv")) {
                $file_size = filesize("$file.csv");
                echo "Output saved to: $file.csv (Size: $file_size bytes)\n";
            }
        }
    } else {
        // Run for all sites (original behavior)
        echo "\033[94mRunning query for all sites:\033[0m\n";
        echo str_repeat("-", 50) . "\n";

        $success_count = 0;
        $failed_count = 0;

        foreach ($sites as $site) {
            $ip = $site['ip'];
            $name = $site['name'];
            $file = $outputDir . $name;
            echo "\033[93mRunning query for $name ................: \033[0m";
            $configFile = __DIR__ . '/.my.cnf';
            $sqlCommand = "mysql --defaults-file=$configFile -h $ip openmrs -e \"" . $file_contents . "\" 2>&1";

            $return_code = 0;
            $output = [];
            exec($sqlCommand, $output, $return_code);

            // Check if output contains 'error' (case-insensitive)
            $output_text = implode("\n", $output);
            $has_error = stripos($output_text, 'error') !== false;

            if ($has_error) {
                echo "\033[31mFailed\033[0m\n";
                $fromError = substr(
                    $output_text,
                    (strpos($output_text, "error") !== false ? strpos($output_text, "error") : strpos($output_text, "ERROR")),
                    900
                );
                echo "Error output: $fromError\n";
                $failed_count++;
            } else {
                echo "\033[32mSuccess\033[0m\n";
                // Convert tabs to commas and save to CSV file
                $csv_content = str_replace("\t", ",", $output_text);
                file_put_contents("$file.csv", $csv_content);
                $success_count++;
            }
        }

        $total_sites = sizeof($sites);
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "\033[32mSummary:\033[0m\n";
        echo "  Total sites: $total_sites\n";
        echo "  Successful: $success_count\n";
        echo "  Failed: $failed_count\n";
    }
} catch (Throwable $th) {
    echo " \033[31m Error: \033[91m" . $th->getMessage();
}
