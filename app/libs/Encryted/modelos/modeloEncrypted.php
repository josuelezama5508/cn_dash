<?php

/* 
************************************************
+                                              +  
+   Desarrollador: JCBL                        +
+   Fecha:  22/02/2021                         +
+   Tipo: V.1.1.2                               +
+                                              +
************************************************   
*/
require_once __dir__ . '/../config/config.php';

class Encryptor
{
    private $CONST;
    private $START;
    private $END;
    private $SPACE;
    private $RANG;

    function __construct()
    {
        $this->CONST = __CONST__;
        $this->START = __START__;
        $this->END = __END__;
        $this->SPACE = __SPACE__;
        $this->RANG = __RANG__;
    }

    public function encryptToken($ARG)
    {
        $TOKEN = '';
        $CIFRADO = $this->nivelCifrado($ARG);
        $i = 0;
        foreach ($ARG as $key => $value) {
            $TOKEN .= $this->encrypt($key . $this->SPACE . $value,  $CIFRADO[$i]);
            $i++;
        }

        $RESPONSE = $TOKEN;

        return $RESPONSE;
    }

    private function encrypt($TXT, $CONTS)
    {

        $KEY_SECRET = $this->CONST[$CONTS];
        $TOKEN = '';

        for ($i = 0; $i < strlen($TXT); $i++) {
            $char = substr($TXT, $i, 1);
            $keychar = substr($KEY_SECRET, ($i % strlen($KEY_SECRET)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $TOKEN .= $char;
        }

        return  base64_encode($TOKEN) . $this->START . $CONTS . $this->END;
    }

    private function nivelCifrado($ARG)
    {
        $NIVEL = array();
        $RANG = (object) $this->RANG;
        foreach ($ARG as $key) {
            $BOOLEAN = false;
            do {
                $NUM = rand($RANG->INICIO, $RANG->FIN);
                if (in_array($NUM, $NIVEL)) {
                    $BOOLEAN = true;
                } else {
                    $BOOLEAN = false;
                }
            } while ($BOOLEAN == true);
            array_push($NIVEL, $NUM);
        }
        return $NIVEL;
    }
}
