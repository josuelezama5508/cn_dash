<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../models/NotificationServiceModel.php';

class NotificationServiceController extends API
{
    private $notifier;

    public function __construct()
    {
        $this->notifier = new NotificationServiceModel();
    }

    public function POST($params = [])
    {
        // Detecta si la acción es enviar notificación
        if (isset($_GET['action']) && $_GET['action'] === 'sendNotification') {
            return $this->sendNotification($params);
        }

        // Si no, guarda la suscripción
        try {
            $rawInput = file_get_contents('php://input');
            $subscription = json_decode($rawInput, true);

            if (!$subscription || !isset($subscription['endpoint'])) {
                return $this->jsonResponse(['message' => 'Suscripción inválida.'], 400);
            }

            $resultado = $this->notifier->guardarSuscripcion($subscription);

            return $this->jsonResponse($resultado, $resultado['status']);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error interno del servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendNotification($params = [])
    {
        try {
            $rawInput = file_get_contents('php://input');
            $payload = json_decode($rawInput, true);
            if (!$payload) {
                $payload = [
                    'title' => 'Nueva reserva creada',
                    'body' => 'Se ha creado una nueva reserva en el sistema.',
                    'icon' => '/icon.png',
                    'url' => 'http://localhost/cn_dash/detalles-reserva/view/'
                ];
            }

            $result = $this->notifier->enviarNotificacion($payload);

            return $this->jsonResponse($result, $result['status']);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error interno del servidor al enviar notificación.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
