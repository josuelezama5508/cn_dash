<?php
class Auth
{
    public static function check()
    {
        return isset($_SESSION['user']);
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            header("Location: " . route('login'));
            exit;
        }
    }

    public static function login($user)
    {
        $_SESSION['user'] = $user;
    }

    public static function logout()
    {
        // session_destroy();
        // Destruir todas las variables de sesión
        $_SESSION = array();

        // Si se desea destruir la sesión completamente, también se debe eliminar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }


        ob_start(); // Iniciar el buffer de salida
        session_start();

        // Finalmente, destruir la sesión
        session_destroy();

        echo '<script> localStorage.clear(); </script>';

        if (!headers_sent()) {
            // Redirigir al usuario a la página de inicio o login
            header('Location: ' . route('login'));
            exit(); // Evita cualquier ejecución posterior
        }
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }
}