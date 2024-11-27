<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: close');
$webhookFile = __DIR__ . '/webhook.txt';
if (file_exists($webhookFile) && trim(file_get_contents($webhookFile)) !== '') {
    $data = file_get_contents($webhookFile);
    $events = explode(PHP_EOL, trim($data));
    $remainingEvents = []; 
    foreach ($events as $event) {
        if (!empty($event)) {
            $eventData = json_decode($event, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "event: webhook-event\n";
                echo "data: " . json_encode($eventData) . "\n\n";
                ob_flush();
                flush();
            } else {
                $remainingEvents[] = $event;
            }
        }
    }
    sleep(5);
    if (!empty($remainingEvents)) {
        file_put_contents($webhookFile, implode(PHP_EOL, $remainingEvents));
    } else {
        file_put_contents($webhookFile, '');
    }
} else {
    echo "event: no-events\n";
    echo "data: No hay eventos pendientes\n\n";
    ob_flush();
    flush();
}
exit;
