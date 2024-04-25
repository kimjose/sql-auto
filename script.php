<?php
try {
    // Get content of the sites
    // get content of the sql file
    if ($argc < 2) {
        echo "Usage: php this_file.php <file_path>\n";
        exit(1);
    }
    $outputDir = "/home/joseph/Documents/test_output/";
    // Get the file path from the command line arguments
    $file_path = $argv[1];

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

    $dbUser = "dwapi";
    $dbPassword = "dwapi";

    $handle = fopen(__DIR__ . '/sites.json', 'r');
    $data = fread($handle, filesize(__DIR__ . '/sites.json'));
    $sites = json_decode($data, true);

    foreach ($sites as $site) {
        $ip = $site['ip'];
        $name = $site['name'];
        $file = $outputDir . $name;
        echo " \033[93m Running query for $name ................:  \033[0m  \n";
        $sqlCommand = "mysql -u $dbUser -p$dbPassword -h $ip openmrs -e \"" . $file_contents . "\" | sed 's/\t/,/g' > $file.csv";
        exec($sqlCommand);
        echo "\n\t";
        echo "\033[32m Success:  \033[0m Query executed successfully. \n";
    }
    $n = sizeof($sites);
    echo "\n\t";
    echo "\033[32m Success:  \033[0m Query executed successfully for $n sites. \n";
} catch (Throwable $th) {
    echo " \033[31m Error: \033[91m" .  $th->getMessage();
}
