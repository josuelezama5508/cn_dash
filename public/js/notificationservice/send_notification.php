<?php
require '/../../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$auth = [
    'VAPID' => [
        'subject' => 'mailto:cotzi3avb@gmail.com', // Cambia por tu email
        'publicKey' => 'BCjPb7dVEemXyruccqydhfkgjhK9eZBzjGT8i6Q49o9HYMyRYscCygePBzqvq_zNU3MI54Mr1-at-j1zlbV8Grc',
        'privateKey' => 'uTq7SsynA854bxlQnlEBXlJNnU1cVTr6Bmr6aYLM1dM',
    ],
];

// Lee todas las suscripciones guardadas
$subscriptions = file('subscriptions.json', FILE_IGNORE_NEW_LINES);

$webPush = new WebPush($auth);

foreach ($subscriptions as $subJson) {
    $subscription = Subscription::create(json_decode($subJson, true));

    $report = $webPush->sendOneNotification(
        $subscription,
        json_encode([
            'title' => 'Nueva reserva creada',
            'body' => 'Se ha creado una nueva reserva en el sistema.',
            'icon' => '/icon.png',
            'url' => 'http://localhost/cn_dash/detalles-reserva/view/' // URL a donde quieres que abra la notificación
        ])
    );

    if ($report->isSuccess()) {
        echo "Notificación enviada a " . $report->getEndpoint() . "\n";
    } else {
        echo "Error al enviar a " . $report->getEndpoint() . ": " . $report->getReason() . "\n";
    }
}
