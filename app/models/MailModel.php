<?php
// MailModel.php

class MailModel
{
    private $lang;
    private $tipo;
    private $data;

    public function __construct($lang = 'es', $tipo = 'confirmacion', $data = [])
    {
        $this->lang = $lang;
        $this->tipo = $tipo;
        $this->data = $data;
    }

    private function getHeader()
    {
        $headers = [
            'es' => [
                'confirmacion' => 'Confirmación de Reserva',
                'cancelacion' => 'Cancelación de Reserva',
                'reagendar' => 'Reagendar Reserva',
                'pagar' => 'Confirmación de Pago',
                'procesado' => 'Procesamiento de Pago',
                'sapa' => 'Solicitud de Asistencia Personalizada',
            ],
            'en' => [
                'confirmacion' => 'Reservation Confirmation',
                'cancelacion' => 'Reservation Cancellation',
                'reagendar' => 'Reschedule Reservation',
                'pagar' => 'Payment Confirmation',
                'procesado' => 'Payment Processing',
                'sapa' => 'Personalized Assistance Request',
            ],
        ];

        return $headers[$this->lang][$this->tipo] ?? 'Notification';
    }

    private function getBody()
    {
        $get = function($key, $default = '') {
            return isset($this->data[$key]) ? $this->data[$key] : $default;
        };

        $bodies = [
            'es' => [
                'confirmacion' => "Hola {$get('cliente_nombre')},\n\nTu reserva {$get('nog')} ha sido confirmada para el {$get('fecha')} a las {$get('hora')}.",
                'cancelacion' => "Hola {$get('cliente_nombre')},\n\nTu reserva {$get('nog')} ha sido cancelada.",
                'reagendar' => "Hola {$get('cliente_nombre')},\n\nTu reserva {$get('nog')} ha sido reagendada para el {$get('nueva_fecha', 'fecha no especificada')} a las {$get('nueva_hora', 'hora no especificada')}.",
                'pagar' => "Hola {$get('cliente_nombre')},\n\nHemos recibido tu pago de {$get('monto')} {$get('moneda')} para la reserva {$get('nog')}.",
                'procesado' => "Hola {$get('cliente_nombre')},\n\nTu pago de {$get('monto')} {$get('moneda')} para la reserva {$get('nog')} ha sido procesado exitosamente.",
                'sapa' => "Hola {$get('cliente_nombre')},\n\nHemos recibido tu solicitud de asistencia personalizada. Nos pondremos en contacto contigo a la brevedad.",
            ],
            'en' => [
                'confirmacion' => "Hello {$get('cliente_nombre')},\n\nYour reservation {$get('nog')} has been confirmed for {$get('fecha')} at {$get('hora')}.",
                'cancelacion' => "Hello {$get('cliente_nombre')},\n\nYour reservation {$get('nog')} has been canceled.",
                'reagendar' => "Hello {$get('cliente_nombre')},\n\nYour reservation {$get('nog')} has been rescheduled to {$get('nueva_fecha', 'date not specified')} at {$get('nueva_hora', 'time not specified')}.",
                'pagar' => "Hello {$get('cliente_nombre')},\n\nWe have received your payment of {$get('monto')} {$get('moneda')} for reservation {$get('nog')}.",
                'procesado' => "Hello {$get('cliente_nombre')},\n\nYour payment of {$get('monto')} {$get('moneda')} for reservation {$get('nog')} has been successfully processed.",
                'sapa' => "Hello {$get('cliente_nombre')},\n\nWe have received your personalized assistance request. We will contact you shortly.",
            ],
        ];

        return nl2br($bodies[$this->lang][$this->tipo] ?? 'Contenido no disponible');
    }

    private function getFooter()
    {
        $footer = "
            <p>Saludos,<br>{$this->data['empresa']}</p>
            <div class=\"social-icons\">
                <a href=\"{$this->data['redes']['facebook']}\"><img src=\"{$this->data['redes']['facebook_icon']}\" alt=\"Facebook\"/></a>
                <a href=\"{$this->data['redes']['instagram']}\"><img src=\"{$this->data['redes']['instagram_icon']}\" alt=\"Instagram\"/></a>
                <a href=\"{$this->data['redes']['twitter']}\"><img src=\"{$this->data['redes']['twitter_icon']}\" alt=\"Twitter\"/></a>
            </div>
            <p>© " . date("Y") . " {$this->data['empresa']}. Todos los derechos reservados.</p>
        ";

        return $footer;
    }

    public function generateEmail()
    {
        $header = $this->getHeader();
        $body = $this->getBody();
        $footer = $this->getFooter();

        return "
            <!DOCTYPE html>
            <html lang=\"{$this->lang}\">
            <head>
                <meta charset=\"UTF-8\" />
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
                <title>{$header}</title>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { width: 100%; max-width: 600px; margin: auto; background-color: #fff; padding: 20px; }
                    .header { background-color: #004085; color: #fff; padding: 10px; text-align: center; }
                    .content { padding: 20px; color: #333; }
                    .footer { background-color: #f9f9f9; padding: 10px; text-align: center; font-size: 12px; color: #777; }
                    .social-icons a { margin: 0 5px; }
                    .social-icons img { width: 20px; height: 20px; }
                </style>
            </head>
            <body>
                <div class=\"email-container\">
                    <div class=\"header\"><h1>{$header}</h1></div>
                    <div class=\"content\">{$body}</div>
                    <div class=\"footer\">{$footer}</div>
                </div>
            </body>
            </html>
        ";
    }
}
?>
