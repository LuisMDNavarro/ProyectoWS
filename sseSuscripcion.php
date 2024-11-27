<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
$filePath = __DIR__ . '/webhook_type3.txt';

while (true) {
    if (file_exists($filePath) && filesize($filePath) > 0) {
        $data = file_get_contents($filePath);
        $events = explode(PHP_EOL, trim($data));
        $newContent = ""; 
        foreach ($events as $event) {
            if (!empty($event)) {
                $eventData = json_decode($event, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "event: webhook_type3\n";
                    echo "data: " . json_encode($eventData) . "\n\n";
                    ob_flush();
                    flush();
                } else {
                    $newContent .= $event . PHP_EOL;
                }
            }
        }
        file_put_contents($filePath, $newContent);
    }
    sleep(20);
}

