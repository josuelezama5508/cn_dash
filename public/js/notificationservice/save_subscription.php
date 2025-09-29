<?php
// Guarda la suscripción que llega en JSON por POST
$subscription = file_get_contents('php://input');

if (!$subscription) {
    http_response_code(400);
    echo json_encode(['error' => 'No subscription data received']);
    exit;
}

// Guardar en archivo JSON (cada suscripción en una línea)
file_put_contents('subscriptions.json', $subscription . PHP_EOL, FILE_APPEND);

echo json_encode(['success' => true]);
