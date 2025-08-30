<?php
class Token
{
    public static function generateToken($user_id)
    {
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../libs/Encryted/modelos/modeloEncrypted.php';

        $model_user = new UserModel();
        $model_encript = new Encryptor();

        $user = $model_user->find($user_id);
        if (!count((array) $user)) return '';

        $__token = $model_encript->encryptToken(array(
            'username' => $user->username,
            'password' => $user->password,
            'id' => $user->id
        ));
        return rawurlencode($__token);
    }

    public static function validateToken($token)
    {
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../libs/Encryted/modelos/modeloDecrypted.php';

        $model_user = new UserModel();
        $model_decript = new Decryptor();

        $result = 0;
        $tokenArray = explode('Bearer ', $token);
        if (count($tokenArray) == 2) {
            $tokenStr = rawurldecode($tokenArray[1]);
            $__token = $model_decript->decryptToken($tokenStr);
            if (count($__token) == 3) {
                $username = $__token['username'];
                $password = $__token['password'];
                $id = $__token['id'];

                $user = $model_user->where("user_id = '$id' AND username = '$username' AND password = '$password'");
                if ($user) $result = intval($user[0]->id);
            }
        }

        return $result;
    }

    public static function renewToken($token)
    {}
}

/*
function generarToken($userId) {
    $token = bin2hex(random_bytes(32)); // Generar un token seguro
    $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour")); // Expira en 1 hora
    
    // Guardar en la BD
    $pdo = new PDO("mysql:host=localhost;dbname=mi_bd", "usuario", "contraseña");
    $stmt = $pdo->prepare("INSERT INTO tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $token, $expiracion]);

    return $token;
}

function validarToken($token) {
    $pdo = new PDO("mysql:host=localhost;dbname=mi_bd", "usuario", "contraseña");
    $stmt = $pdo->prepare("SELECT * FROM tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(403); // Token inválido o expirado
        echo json_encode(["error" => "Token inválido o ha expirado"]);
        exit();
    }

    return true; // Token válido
}

function renovarToken($token) {
    $nuevaExpiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $pdo = new PDO("mysql:host=localhost;dbname=mi_bd", "usuario", "contraseña");
    $stmt = $pdo->prepare("UPDATE tokens SET expires_at = ? WHERE token = ?");
    $stmt->execute([$nuevaExpiracion, $token]);

    return json_encode(["mensaje" => "Token renovado con éxito"]);
}

 */