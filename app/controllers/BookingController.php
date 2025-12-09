<?php
require_once __DIR__ . "/../../app/core/Controller.php";
require_once __DIR__ . "/../models/UserModel.php";

class BookingController extends Controller
{
    // public function create(...$param)
    // {
    //     Auth::requireLogin();
    //     /*if (intval($param[0]) == 0) {
    //         header("Location: " . route('inicio'));
    //     } else {
    //         // if ($param[1] == 0) header("Location: " . route('inicio'));

    //         $this->view('dashboard/view_booking', ["company" => $param[0], "product" =>  $param[1]]);
    //     }*/

    //     if (!count($param) || count($param) < 2)
    //         header("Location: " . route('inicio'));

    //     $companyCode = $param[0];
    //     $productCode = $param[1];
        
    //     $this->view('dashboard/view_booking', ["company" => $companyCode, "product" => $productCode]);
    // }
    public function create(...$param)
    {
        Auth::requireLogin();
        // Obtener el token de la sesión (suponiendo que guardaste en $_SESSION['user'] el token)
        $tokenHeader = ['Authorization' => 'Bearer ' . Auth::user()];
            
        $userModel = new UserModel();
        $userInfo = $userModel->getUserIdAndLevelByToken($tokenHeader);

        if ($userInfo['status'] !== 'SUCCESS') {
            die($userInfo['message']); // o redirigir a login
        }
        // Si no vienen parámetros por URL, leer desde POST
        if ((!count($param) || count($param) < 2) && isset($_POST['company']) && isset($_POST['product'])) {
            $param[0] = $_POST['company'];
            $param[1] = $_POST['product'];
        }

        // Si aún no hay datos válidos
        if (!isset($param[0]) || !isset($param[1])) {
            header("Location: " . route('inicio'));
            exit;
        }

        $companyCode = $param[0];
        $productCode = $param[1];

        $this->view('dashboard/view_booking', [
            "company" => $companyCode,
            "product" => $productCode,
            'user_id' => $userInfo['data']['user_id'],
            'level'   => $userInfo['data']['level']
        ]);
    }


    public function successConfirm(){
        Auth::requireLogin();
        $this->view('dashboard/view_home');
    }
}
// require_once __DIR__ . "/../../app/core/Controller.php";
// require_once __DIR__ . "/../models/RoutesModel.php";

// class BookingController extends Controller
// {
//     public function create(...$param)
//     {
//         Auth::requireLogin();

//         if (!count($param)) {
//             header("Location: " . route('inicio'));
//             exit;
//         }

//         $slug = $param[0];

//         // $routeModel = new RoutesModel();
//         // $route = $routeModel->getRouteBySlug($slug);

//         // if (!$route) {
//         //     header("Location: " . route('inicio'));
//         //     exit;
//         // }

//         $this->view('dashboard/view_booking', [
//             "slug"    => $slug
//         ]);
//     }
// }
