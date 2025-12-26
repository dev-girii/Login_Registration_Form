<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    // Create manager and run a ping command
    $manager = new MongoDB\Driver\Manager($MONGO_URI);
    $cmd = new MongoDB\Driver\Command(['ping' => 1]);
    $cursor = $manager->executeCommand($MONGO_DB, $cmd);
    $result = current($cursor->toArray());
    echo "MongoDB ping OK.\n";
    echo "Raw result: " . json_encode($result) . "\n";
} catch (Exception $e) {
    echo "MongoDB connect failed: " . $e->getMessage() . "\n";
    // Helpful troubleshooting hints
    echo "Hints:\n";
    echo " - Make sure MongoDB server is running and listening on 127.0.0.1:27017\n";
    echo " - On Windows, check `net start` or `sc query MongoDB` and use `net start MongoDB` to start the service if installed.\n";
    echo " - Check port: run `netstat -ano | findstr 27017` in cmd.exe.\n";
    echo " - If MongoDB isn't installed, install MongoDB Community Server or run a docker container: docker run -d --name mongodb -p 27017:27017 mongo:6.0\n";
}
