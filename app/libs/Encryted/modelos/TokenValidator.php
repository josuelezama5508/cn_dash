<?php

require_once __DIR__ . '/modeloDecrypted.php'; // Ajusta si es necesario la ruta
require_once __DIR__ . '/../../../models/UserModel.php';
class TokenValidator
{
    private $decryptor;

    public function __construct()
    {
        $this->decryptor = new Decryptor();
    }

    /**
     * Valida un token: decodifica y verifica si es válido.
     * Retorna datos decodificados o false si no es válido.
     */
    public function validateToken($token)
    {
        if (empty($token)) {
            return false;
        }

        // Decodifica URL para evitar problemas con caracteres %XX
        $decodedToken = urldecode($token);

        $data = $this->decryptor->decryptToken($decodedToken);

        // Si devuelve error, el token no es válido
        if (isset($data['status']) && $data['status'] === 'ERROR') {
            return false;
        }

        return $data;
    }
}
