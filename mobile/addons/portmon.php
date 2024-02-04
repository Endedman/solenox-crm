<?php
$services = [
    ['host' => 'j2me.xyz', 'port' => 5190, 'service' => 'AIM server'],
    ['host' => 'j2me.xyz', 'port' => 5195, 'service' => 'AIM 2.x server'],
    ['host' => 'j2me.xyz', 'port' => 25565, 'service' => 'MC-Aperture-1 server'],
    ['host' => 'j2me.xyz', 'port' => 27960, 'service' => 'ioQuake3a server'],
    // Add or modify services here
];

$results = [];

foreach ($services as $service) {
    $connection = @fsockopen($service['host'], $service['port'], $errno, $errstr, 2); // Timeout 2 seconds

    if (is_resource($connection)) {
        $results[$service['service']] = "[OK] {$service['host']}:{$service['port']} - {$service['service']} is working";
        fclose($connection);
    } else {
        $results[$service['service']] = "[Failed] {$service['host']}:{$service['port']} - {$service['service']} has failed \n";
    }
}

// Generate HTML for the port statuses
foreach ($results as $service => $result) {
    $statusClass = strpos($result, '[OK]') !== false ? 'success' : 'error';
    echo '<li class="' . htmlspecialchars($statusClass) . '">' . htmlspecialchars($result) . '</li>';
}
?>
