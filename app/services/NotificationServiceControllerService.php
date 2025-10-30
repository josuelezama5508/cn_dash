<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../repositories/NotificationServiceRepository.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationServiceControllerService
{
    private $notificationservice_repo;
    private $auth;

    public function __construct()
    {
        $this->notificationservice_repo = new NotificationServiceRepository();

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
        $existentes = $this->notificationservice_repo->findByEndpoint($subscription['endpoint']);
        if (!empty($existentes)) {
            return ['status' => 200, 'message' => 'La suscripción ya existe.'];
        }

        $data = [
            'endpoint' => $subscription['endpoint'],
            'p256dh'   => $subscription['keys']['p256dh'] ?? null,
            'auth'     => $subscription['keys']['auth'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $insert = $this->notificationservice_repo->save($data);

        return $insert
            ? ['status' => 201, 'message' => 'Suscripción guardada correctamente.']
            : ['status' => 500, 'message' => 'Error al guardar la suscripción.'];
    }

    public function enviarNotificacion(array $payload)
    {
        $subs = $this->notificationservice_repo->getAll();
        if (empty($subs)) {
            return ['status' => 404, 'message' => 'No hay suscripciones para enviar notificaciones'];
        }

        $webPush = new WebPush($this->auth);
        $results = [];

        foreach ($subs as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => [
                    'p256dh' => $sub->p256dh,
                    'auth' => $sub->auth
                ]
            ]);

            $report = $webPush->sendOneNotification($subscription, json_encode($payload));

            $results[] = [
                'endpoint' => $report->getEndpoint(),
                'status' => $report->isSuccess() ? 'success' : 'fail',
                'reason' => $report->getReason() ?? null
            ];
        }

        return ['status' => 200, 'results' => $results];
    }
    public function procesarEnvioNotificacion($params = [])
    {
        try {
            if (!empty($params['payload'])) {
                $payload = $params['payload'];
            } else {
                $rawInput = file_get_contents('php://input');
                $payload = json_decode($rawInput, true);
    
                if (!$payload) {
                    $payload = [
                        'title' => 'Nueva reserva creada',
                        'body'  => 'Se ha creado una nueva reserva en el sistema.',
                        'icon'  => '/icon.png',
                        'url'   => 'http://localhost/cn_dash/detalles-reserva/view/'
                    ];
                }
            }
    
            return $this->enviarNotificacion($payload);
    
        } catch (Exception $e) {
            return [
                'status' => 500,
                'message' => 'Error interno del servidor al procesar la notificación.',
                'error' => $e->getMessage()
            ];
        }
    }
    

}
