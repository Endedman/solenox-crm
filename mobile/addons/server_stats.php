<?php
// server_stats.php

header('Content-Type: application/json');

function get_server_memory_usage() {
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;

    return $memory_usage;
}

function get_disk_usage() {
    $disk_free = disk_free_space("/");
    $disk_total = disk_total_space("/");
    $disk_usage = 100 - ($disk_free / $disk_total) * 100;

    return $disk_usage;
}
function get_ftp_usage() {
    $ftp_free = disk_free_space("/ftpfs");
    $ftp_total = disk_total_space("/ftpfs");
    $ftp_usage = 100 - ($ftp_free / $ftp_total) * 100;

    return $ftp_usage;
}

function get_cpu_usage() {
    // The command and parsing logic might differ based on your server's `mpstat` output
    $mpstat_output = shell_exec('mpstat 1 1');
    if ($mpstat_output === null) {
        throw new RuntimeException("Unable to retrieve mpstat data.");
    }

    // Parse the output to get the idle CPU percentage from the 'Average' line
    if (preg_match('/Average:.*?all.*?(\d+\.\d+)$/', $mpstat_output, $matches)) {
        $cpuIdle = floatval($matches[1]);
    } else {
        throw new RuntimeException("Unable to parse CPU idle percentage.");
    }
    $cpuUsage = 100 - $cpuIdle;

    return $cpuUsage;
}

try {
    $memory_usage = get_server_memory_usage();
    $disk_usage = get_disk_usage();
    $ftp_usage = get_ftp_usage();

    $cpu_usage = get_cpu_usage();

    echo json_encode([
        'memory_usage' => $memory_usage,
        'disk_usage' => $disk_usage,
        'ftp_usage' => $ftp_usage,
        'cpu_usage' => $cpu_usage
    ]);
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
