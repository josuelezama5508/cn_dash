<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . '/../services/NotificationServiceControllerService.php';

class NotificationServiceController extends API
{
    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationServiceControllerService();
    }

    public function POST($params = [])
    {
        // Detecta si la acción es enviar notificación
        if (isset($_GET['action']) && $_GET['action'] === 'sendNotification') {
            return $this->sendNotification($params);
        }

        // Guardar suscripción
        try {
            $rawInput = file_get_contents('php://input');
            $subscription = json_decode($rawInput, true);
            if (!$subscription || !isset($subscription['endpoint'])) {
                return $this->jsonResponse(['message' => 'Suscripción inválida.'], 400);
            }

            $resultado = $this->notificationService->guardarSuscripcion($subscription);

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
            $result = $this->notificationService->procesarEnvioNotificacion($params);
            return $this->jsonResponse($result, $result['status']);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => 'Error interno del servidor al enviar notificación.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
