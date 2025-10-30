<?php
require_once __DIR__ . '/app/models/MailModel.php';
require_once __DIR__ . '/app/models/TemplatesMailModel.php';
require_once __DIR__ . '/app/models/Models.php';

$template = new TemplatesMailModel();
$model_control = new Control();
$model_bookingdetails = new BookingDetails();
$model_empresa = new Empresa();
$model_producto = new Productos();
$model_empresainfo = new EmpresaInfo();

function parseItemsByTipo($items_details) {
    // Asegurarse de que esté como array
    if (is_string($items_details)) {
        $items = json_decode($items_details, true);
    } else {
        $items = $items_details;
    }

    $tours = [];
    $addons = [];
    $pax = 0;
    foreach ($items as $item) {

        $formatted = "{$item['item']} x {$item['name']}";
        $formattedAddon = "{$item['item']} x {$item['name']}";
        if ($item['tipo'] === 'tour') {
            $tours[] = $formatted;
        } elseif ($item['tipo'] === 'addon') {
            $addons[] = $formatted;
        }
        
        $pax += (int)$item['item'];
    }

    return [
        'tours' => $tours,
        'addons' => $addons,
        'pax' => $pax
    ];
}
function obtenerDominioLimpio($url) {
    // Asegúrate de que tenga esquema para que parse_url funcione bien
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'http://' . $url;
    }

    $host = parse_url($url, PHP_URL_HOST);

    // Eliminar "www." si existe
    $host = preg_replace('/^www\./', '', $host);

    return $host;
}
function fechaConNombre($fecha, $idioma = 'en') {
    $timestamp = strtotime($fecha);
    if (!$timestamp) return 'Fecha inválida';

    $meses = [
        'es' => [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ],
        'en' => [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ]
    ];

    $dia = date('d', $timestamp);
    $mes = $meses[$idioma][date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);

    if ($idioma === 'es') {
        return "$dia de $mes del $anio";
    } else {
        return "$mes $dia, $anio";
    }
}

function getByBookingData($idpago){
    global $model_control, $model_bookingdetails, $model_empresa, $model_producto, $model_empresainfo;
    $dataControl = $model_control->find($idpago);
    $dataBooking = $model_bookingdetails->where("idpago = :idpago",['idpago' => $idpago]);
    $bookinDetail= $dataBooking[0] ?? null;
    $dataEmpresa = $model_empresa->getCompanyByCode($dataControl->code_company);
    $empresa = $dataEmpresa[0] ?? null;
    $dataProduct =  $model_producto->find($dataControl->product_id);
    $items = parseItemsByTipo($bookinDetail->items_details);
    $dataEmpresaInfo = $model_empresainfo->where('empresa_id = :id', ['id'=>$empresa->id]);
    $empresaInfo = $dataEmpresaInfo[0] ?? null;
    // Devolver los datos necesarios para tiketConfirm
    return [
        'website' => $empresa->website ?? 'http://www.totalsnorkelcancun.com',
        'webname' => obtenerDominioLimpio($empresa->website ?? 'http://www.totalsnorkelcancun.com'),
        'company_logo' => $empresa->company_logo ?? "https://www.totalsnorkelcancun.com/img/logo-snorkel.png",
        'empresa_id' => $empresa->id,
        'datepicker' => fechaConNombre($dataControl->datepicker, ($dataProduct->lang_id === 1) ? 'en' : 'es'),
        'leng' => ($dataProduct->lang_id === 1) ? 'en' : 'es',
        'moneda' => $dataControl->moneda,
        'estado' => $dataControl->estado ?? 0,
        'nog' => $dataControl->nog ?? '',
        'actividad' => $dataProduct->product_name ?? 'Sin nombre',
        'time' => $dataControl->horario ?? '',
        'addons' => $items['addons'],
        'tickets'=> $items['tours'],
        'pax' => $items['pax'],
        'total' => $dataControl->total,
        'referencia' => $dataControl->referencia ?? '',
        'email' => $dataControl->email ?? '',
        'dock_fee' => 10.00, // Tal cual, sin ?? fallback
        'telefono' => $dataControl->telefono ?? '',
        'primary_color' => $empresa->primary_color ?? '#ec008c',
        'secondary_color' => $empresa->secondary_color ?? '#000',
        'cliente_name' => $dataControl->cliente_name . " " . $dataControl->cliente_lastname,
        "tel" => [
				"en" => $empresaInfo ? $empresaInfo->telefono_en : "998 23 40 870",
				"es" => $empresaInfo ? $empresaInfo->telefono_es : "998 23 40 871"
			],
        "social" => $empresaInfo ? $empresaInfo->social :
        '
				<a href="https://www.facebook.com/TotalSnorkelCancun/" style="display:inline-block;text-decoration:none">
					<img src="https://www.totalsnorkelcancun.com/img/iconos/red-1.png" style="height:28px">
				</a>
				<a href="https://www.tripadvisor.com.mx/Attraction_Review-g150807-d7171248-Reviews-Total_Snorkel_Cancun-Cancun_Yucatan_Peninsula.html" style="display:inline-block;text-decoration:none">
					<img src="https://www.totalsnorkelcancun.com/img/iconos/red-3.png" style="height:28px">
				</a>
				<a href="https://twitter.com/totalsnorkel?lang=es" style="text-decoration:none;display:inline-block">
					<img src="https://www.totalsnorkelcancun.com/img/iconos/red-4.png" style="height:28px">
				</a>
				<a href="https://www.youtube.com/channel/UCCkyPh5Hv71V-HbYlo74NWQ" style="display:inline-block;text-decoration:none">
					<img src="https://www.totalsnorkelcancun.com/img/iconos/red-5.png" style="height:28px">
				</a>
				<a href="https://www.instagram.com/totalsnorkelcancun/" style="display:inline-block;text-decoration:none">
					<img src="https://www.totalsnorkelcancun.com/img/insta.png" style="height:29px">
				</a>
			',
            
        
        
    ];
}

$data = getByBookingData(11);
// echo json_encode($data);
if ($data) {
    $html = $template->tiketConfirm($data);
    echo $html;
} else {
    echo "No se encontraron datos para ese ID.";
}
