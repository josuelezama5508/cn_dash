<?php
class GCS_SSL
{
    private $CANT_KEY;

    function __construct($CANTIDAD)
    {
        $this->CANT_KEY = $CANTIDAD;
    }

    public function generarArchivo()
    {
        $ARCHIVO = '/../config/KEY_SECRECT.ini';
        $OPEN = fopen(__DIR__ .$ARCHIVO, "w+");
        $KEY_SECRECT = $this->arrayClaves();
        $FECHA = date('l jS \of F Y h:i:s A');
        fwrite($OPEN, '[__CONST__]' . PHP_EOL);
        fwrite($OPEN, "; ================================================== Desarrollado en el  $FECHA ================================================== " . PHP_EOL);
        foreach ($KEY_SECRECT as $key => $value) {
            fwrite($OPEN, "{$key}={$value}" . PHP_EOL);
        }
        fwrite($OPEN, "; ================================================== Desarrollado en el $FECHA ================================================== " . PHP_EOL);
    }

    public function arrayClaves()
    {
        $key_secrets = array();
        while (count($key_secrets) < $this->CANT_KEY) {
            $long = rand(40, 70);
            $token = $this->generarClave($long);
            if (!in_array($token, $key_secrets)) {
                array_push($key_secrets, $token);
            }
        }
        return $key_secrets;
    }

    public function generarClave($ITERACION)
    {
        $chars = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ1023456789@.";
        $var_size = strlen($chars);
        $random_str = '';
        for ($x = 0; $x <= $ITERACION; $x++) {
            $random_str .= $chars[rand(0, $var_size - 1)];
        }
        return  $random_str;
    }
}
/* 
$SEGURIDAD = new GCS_SSL(1000);
$SEGURIDAD->generarArchivo();
 */