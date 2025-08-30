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

class Decryptor
{
    private $CONST;
    private $START;
    private $END;
    private $SPACE;

    function __construct()
    {
        $this->CONST = __CONST__;
        $this->START = __START__;
        $this->END = __END__;
        $this->SPACE = __SPACE__;
    }

    public function decryptToken($TOKEN)
    {
        $RESP = array();
        $ARRAY_TOKEN = $this->getDatosToken($TOKEN);

        foreach ($ARRAY_TOKEN as $key => $value) {
            $ELEMENT =  explode($this->SPACE, $this->decrypt($key, $value));
            $RESP[$ELEMENT[0]] = $ELEMENT[1];
        }

        if($RESP == []){
            $RESP['status'] = 'ERROR';
            $RESP['mensaje'] = 'Token invalido';
            $RESP['PROCESS'] = 'TRANSACCION - PASO 4';
        }else{
            $RESP['status'] = 'SUCCESS';
        }
        return $RESP;
    }

    private function decrypt($CONTS, $TXT)
    {
        $KEY_SECRET = $this->CONST[$CONTS];
        $ARG = base64_decode($TXT);
        $RESULTADO = '';
        for ($i = 0; $i < strlen($ARG); $i++) {
            $char = substr($ARG, $i, 1);
            $keychar = substr($KEY_SECRET, ($i % strlen($KEY_SECRET)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $RESULTADO .= $char;
        }
        return $RESULTADO;
    }

    private function getDatosToken($TOKEN)
    {
        $RESPONSE = array();
        $ARREGLO_TOKEN = preg_split('/' . $this->START . '([0-9]*)' . $this->END . '/', $TOKEN);
        $CIFRADO = $this->getCifrado($TOKEN);

        foreach ($CIFRADO as $key => $value) {
            $RESPONSE[$value] = $ARREGLO_TOKEN[$key];
        }

        return  $RESPONSE;
    }

    private function getCifrado($TOKEN)
    {
        $CIFRADO = array();
        $EXPRE = '/' . $this->START . '([0-9]*)' . $this->END . '/';
        preg_match_all($EXPRE, $TOKEN, $CIFRADO);
        return $CIFRADO[1];
    }
}

/* $sd = new Decryptor();
print_r($sd->decryptToken('t7rDon6peEmurH6bjXW/rY4=¡8&x3aJlI66epK0qo+hjneCkbR8k5h7e7CEmGh8m5dqkA==¡28&na3LeoiSW6mjbIScjK9znKWtiFZlgnNTeX8=¡19&uY2PmKmjY6eAb5qqhYyii6M=¡78&i83Qnp9+d7SJf26Odw==¡55&ysawlLm1x3+mom5tXZywsIuGeISOkqux¡89&')); */