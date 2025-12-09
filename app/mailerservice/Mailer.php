<?php

namespace App\MailerService;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
    public $error = null;
    public $status = false;
    private $mail;

    // Correos que NO deben recibir nada
    private $exclude = [
        'frontdesk@totalsnorkelcancun.com',
        'reservas1@totalsnorkelcancun.com',
        'reserva2@totalsnorkelcancun.com',
        'reserva3@totalsnorkelcancun.com',
        'reservas4@totalsnorkelcancun.com',

        // Añados los comentados
        // 'captain@snorkelking.com',
        // 'newsletter@totalsnorkelcancun.com'
    ];

    // Copias ocultas obligatorias
    private $forcedBCC = [
        'cotzi3avb@gmail.com'
        // 'sistemas@snorkeladventure.com',
        // 'sistemas@cancunrivieramaya.com',
        // 'sistemas@parasailcancun.com',
        // 'valdezcinthia982@gmail.com',
        // 'sistemas@oceanix.com.mx',
        // 'fly@parasailcancun.com',
        // 'sky@parasailcancun.com',

        // También agrego tus comentados obligatorios
        // 'monitoreo@totalsnorkelcancun.com',
        // 'cintia.valdez@totalsnorkelcancun.com',
        // 'backup@snorkeladventure.com',
        // 'auditoria@totalsnorkelcancun.com'
    ];

    /**
     * Constructor con FROM dinámico
     */
    public function __construct(
        string $set_from = 'fish@totalsnorkelcancun.com',
        string $name = 'Total Snorkel Cancun'
    ) {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Host = 'appsnorkel.totalsnorkelcancun.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'no-replay@appsnorkel.totalsnorkelcancun.com';
        $this->mail->Password = '@mailpsstsc0101';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = 465;

        $this->mail->isHTML(true);
        $this->mail->setFrom($set_from, $name);

        // Cabecera custom original
        $this->mail->XMailer = "Total Snorkel API Mail 1.0";
        $this->mail->addCustomHeader('X-CRM-SPT', base64_encode($_SERVER['HTTP_HOST'] . ($_SERVER['PHP_SELF'] ?? '/')));
    }
    public function setFrom(string $email, string $name = "")
    {
        try {
            $this->mail->setFrom($email, $name ?: $email);
        } catch (Exception $e) {
            $this->error = "Error al asignar FROM: " . $e->getMessage();
        }
    }
    
    public function setSubject(string $subject)
    {
        $this->mail->Subject = $subject ?: 'Sin Asunto';
    }
    
    /**
     * Envío de correo
     */
    public function sendMail(
        string $asunto = "",
        string $cuerpo = "",
        string $nohtml = "",
        array $adjuntos = [],
        array $destinatario = [],
        array $copia = []
    ) {
        try {
            $this->mail->Subject = $asunto ?: 'Sin Asunto';
            $this->mail->AltBody = $nohtml ?: 'Informacion extra, no disponible';
            $this->mail->Body = $cuerpo ?: ('Sin cuerpo de mensaje \n' . json_encode($_SERVER));

            // Adjuntos
            foreach ($adjuntos as $name => $dir) {
                $this->mail->addAttachment($dir, $name);
            }

            // Destinatarios
            if (!empty($destinatario)) {
                foreach ($destinatario as $name => $email) {
                    if (!$email || in_array($email, $this->exclude)) {
                        continue;
                    }

                    if (is_numeric($name)) {
                        $this->mail->addAddress($email);
                    } else {
                        $this->mail->addAddress($email, $name);
                    }
                }
            }

            // Si no hay destinatarios
            if (empty($destinatario)) {
                $this->mail->addAddress("sistemas@totalsnorkelcancun.com", 'Total Snorkel Cancun');
            }

            // Copias externas agregadas manualmente
            foreach ($copia as $email) {
                if ($email && !in_array($email, $this->exclude)) {
                    $this->mail->addBCC($email);
                }
            }

            // Copias obligatorias
            foreach ($this->forcedBCC as $bcc) {
                $this->mail->addBCC($bcc);
            }

            // Envío
            $this->status = $this->mail->send();
            $this->error = $this->mail->ErrorInfo;

            return $this->status;
        } catch (Exception $ex) {
            $this->error = $this->mail->ErrorInfo;
            $this->status = false;
        }

        // Logging
        if ($this->error) {
            $logFile = fopen(__DIR__ . "/log.txt", 'a');
            fwrite($logFile, "\n" . date("Y-m-d H:i:s") . " " . $this->mail->ErrorInfo);
            fclose($logFile);
        }

        return $this->status;
    }
}
