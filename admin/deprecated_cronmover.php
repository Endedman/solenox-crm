<?php
$envDir = '/var/www/html/jstore/';
require_once $envDir . 'config.php';
$files = glob(JSTORE_TEMP_UPLOAD_DIR . "*");

foreach ($files as $file) {
    $destination_file = JSTORE_UPLOAD_DIR . basename($file);

    if (!file_exists($destination_file)) {
        if (rename($file, $destination_file)) {
            echo "Moved $file to $destination_file.\n";
        } else {
            echo "Failed to move $file.\n";
        }
    }
}