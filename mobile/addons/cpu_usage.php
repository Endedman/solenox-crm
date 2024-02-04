<?php
// cpu_usage.php

header('Content-Type: application/json');

function get_cpu_usage() {
    // Run `mpstat` to gather CPU usage statistics
    $mpstat_output = shell_exec('mpstat 1 1');
    if ($mpstat_output === null) {
        throw new RuntimeException("Unable to retrieve mpstat data.");
    }
    
    // Parse the output to get the idle CPU percentage from the 'Average' line
    // The regex pattern searches specifically for the line that starts with 'Average:'
    if (preg_match('/Average:.*?all.*?(\d+\.\d+)/', $mpstat_output, $matches)) {
        $cpuIdle = $matches[1];
        $cpuUsage = 100 - $cpuIdle;
    } else {
        throw new RuntimeException("Unable to parse CPU idle percentage.");
    }
    
    return $cpuUsage;
}

try {
    $cpu_usage = get_cpu_usage();
    echo json_encode(['cpu_usage' => $cpu_usage]);
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
