<?php

$composer = json_decode(file_get_contents(__DIR__ . '/modules.json'), true);
foreach ($composer['require'] as $component => $version) {
    $testDir = __DIR__ . "/Module/$component/test";
    if (file_exists($testDir) == false) {
        continue;
    }
    foreach (scandir($testDir) as $item) {
        if (strpos($item, ".json") === false) {
            continue;
        }
        $file = $testDir . '/' . $item;
        runTest($file, $argv);
    }
}
echo "\nTEST SUCCESS\n";

function runTest($file, $argv) {
    $envFile = isset($argv[1]) ? $argv[1] : NULL;
    if (!$envFile) {
        echo "\n Usage: php -f test.php default_ev.json";
        die;
    }
    
    exec("newman run \"$file\" -e \"$envFile\"", $stdout, $retVal);
    foreach ($stdout as $line) {
        echo "\n$line";
    }
    if ($retVal != 0) {
        echo "\nTEST FAIL: $file\n";
        die;
    }
}
