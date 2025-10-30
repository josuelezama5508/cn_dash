<?php
require_once 'app/api/Models/MailModel.php';

$lang = 'es';
$tipo = 'pagar';
$data = [
  "subject" => "Pago recibido",
  "cliente_nombre" => "María",
  "monto" => "100.00",
  "moneda" => "USD",
  "nog" => "ABC123",
  "actividad" => "Tour X",
  "fecha" => "2025-10-20",
  "hora" => "14:30",
  "empresa" => "MiEmpresa",
  "redes" => [
    "facebook" => "https://facebook.com/miempresa",
    "instagram" => "https://instagram.com/miempresa",
    "twitter" => "https://twitter.com/miempresa",
    "facebook_icon" => "/icons/facebook.png",
    "instagram_icon" => "/icons/instagram.png",
    "twitter_icon" => "/icons/twitter.png"
  ]
];

// Crear instancia de MailModel
$mailModel = new MailModel($lang, $tipo, $data);

// Generar el HTML del correo
$html = $mailModel->generateEmail();

// Mostrar el HTML generado (para pruebas)
echo $html;

// Luego usar $html para enviarlo con tu sistema SMTP o PHPMailer
?>
