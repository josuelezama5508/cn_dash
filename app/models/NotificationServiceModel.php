<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationServiceModel
{
    private $filePath;
    private $auth;

    public function __construct()
    {
        $folder = dirname(__DIR__, 2) . '/notification';
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $this->filePath = $folder . '/subscriptions.json';

        $this->auth = [
            'VAPID' => [
                'subject' => 'mailto:cotzi3avb@gmail.com',
                'publicKey' => 'BCjPb7dVEemXyruccqydhfkgjhK9eZBzjGT8i6Q49o9HYMyRYscCygePBzqvq_zNU3MI54Mr1-at-j1zlbV8Grc',
                'privateKey' => 'uTq7SsynA854bxlQnlEBXlJNnU1cVTr6Bmr6aYLM1dM',
            ],
        ];
    }

    public function guardarSuscripcion(array $subscription)
    {
        $subscriptions = [];

        if (file_exists($this->filePath)) {
            $content = file_get_contents($this->filePath);
            $subscriptions = json_decode($content, true);
            if (!is_array($subscriptions)) {
                $subscriptions = [];
            }
        }

        foreach ($subscriptions as $existing) {
            if ($existing['endpoint'] === $subscription['endpoint']) {
                return [
                    'status' => 200,
                    'message' => 'La suscripción ya existe.'
                ];
            }
        }

        $subscriptions[] = $subscription;
        file_put_contents($this->filePath, json_encode($subscriptions, JSON_PRETTY_PRINT));

        return [
            'status' => 201,
            'message' => 'Suscripción guardada correctamente.'
        ];
    }

    // Nueva función para enviar notificaciones a todas las suscripciones guardadas
    public function enviarNotificacion(array $payload)
    {
        if (!file_exists($this->filePath)) {
            return ['status' => 404, 'message' => 'No se encontraron suscripciones'];
        }

        $subscriptionsRaw = file_get_contents($this->filePath);
        $subscriptions = json_decode($subscriptionsRaw, true);
        if (!is_array($subscriptions) || empty($subscriptions)) {
            return ['status' => 404, 'message' => 'No hay suscripciones para enviar notificaciones'];
        }

        $webPush = new WebPush($this->auth);
        $results = [];

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create($sub);
            $report = $webPush->sendOneNotification(
                $subscription,
                json_encode($payload)
            );

            if ($report->isSuccess()) {
                $results[] = ['endpoint' => $report->getEndpoint(), 'status' => 'success'];
            } else {
                $results[] = ['endpoint' => $report->getEndpoint(), 'status' => 'fail', 'reason' => $report->getReason()];
            }
        }

        return ['status' => 200, 'results' => $results];
    }
}
