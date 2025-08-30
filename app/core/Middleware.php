<?php
class Middleware
{
    public static function blockMobile()
    {
        // $regex = '/(android|iphone|ipad|mobile|tablet)/i';
        $regex = '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i';
        if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
            http_response_code(403);
            echo "Acceso denegado desde dispositivos móviles.";
            // echo "<h2>Acceso denegado</h2><p>Este sistema no está disponible en dispositivos móviles.</p>";
            exit;
        }
    }
}
