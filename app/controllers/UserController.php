<?php
require_once __DIR__ . "/../../app/core/Controller.php";


class UserController extends Controller
{
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function login()
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['post', 'POST'])) {
            header('Content-Type: application/json');

            try {
                $response = array("message" => "Error en los datos enviados.");
                $httpCode = 400;

                $data = json_decode(file_get_contents("php://input"), true);
                if (!isset($data)) return $this->jsonResponse(["message" => "Error en las credenciales enviadas."], 400);

                $url = domain() . '/api/user/login';  // URL de la API
                $ch = curl_init($url); // Inicializar cURL

                // Configurar opciones
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Obtener respuesta en lugar de imprimirla
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Establecer tipo de contenido
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Si la API usa HTTPS, intenta desactivar temporalmente la verificación SSL

                $response = json_decode(curl_exec($ch)); // Ejecutar la solicitud
                if (curl_errno($ch)) {
                    echo $this->jsonResponse(['Error en cURL: ' . curl_error($ch)], $httpCode);
                }

                curl_close($ch); // Cerrar la conexión
                $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

                if ($httpCode == 200) {
                    Auth::login($response->__token);
                    $response->redirect = domain() . '/inicio';
                }

                return $this->jsonResponse($response, $httpCode);
            } catch (Exception $e) {
                return $this->jsonResponse(array('message' => 'No tienes permisos para acceder al recurso.'), 403);
            }
        }

        if (Auth::check())
            header("Location: " . route('inicio'));

        $this->view('user/view_login', ['error' => $error ?? null]);
    }

    public function logout()
    {
        Auth::logout();
        header("Location: " . route('login'));
    }
}