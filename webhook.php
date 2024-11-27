<?php
header('Content-Type: application/json');
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "JSON inválido."]);
    exit;
}

if (!isset($data['type'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el campo 'type'."]);
    exit;
}

switch ($data['type']) {
    case 1:
        file_put_contents(__DIR__ . "/webhook.txt", json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
        echo json_encode([
            "status" => "success",
            "message" => "Notificación de registro procesada."
        ]);
        break;
    case 2:
        file_put_contents(__DIR__ . "/webhook_type2.txt", json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
        echo json_encode([
            "status" => "success",
            "message" => "Notificación de nuevo contenido"
        ]);
        break;
    case 3:
        file_put_contents(__DIR__ . "/webhook_type3.txt", json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
        echo json_encode([
            "status" => "success",
            "message" => "Notificación de suscripción"
        ]);
        break;
    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Tipo no reconocido."]);
        break;
}
