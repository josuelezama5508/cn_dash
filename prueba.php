<?php
session_set_cookie_params(time() + 86400, '/', 'www.totalsnorkelcancun.com');
session_start();
if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
} else { ?>
    <script type="text/javascript">
        window.location.href = 'https://www.totalsnorkelcancun.com/dash/';
    </script>
<?php }

include_once(__DIR__ . "/../src/model/Modelos.php");

$model_empresas = new Empresa();
$model_disponibilidad = new Disponibilidad();
$model_dispo_bloq = new DispoBlock();

$dias_activos = array(
    "Mon" => "Lunes",
    "Tue" => "Martes",
    "Wed" => "Miercoles",
    "Thu" => "Jueves",
    "Fri" => "Viernes",
    "Sat" => "Sabado",
    "Sun" => "Domingo"
);

$bds = array(
    'parasail' => 'Total Snorkel',
    'gama984' => 'Parasail Cancun'
);

if ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'get') {
    $view = isset($_REQUEST['view']) ? $_REQUEST['view'] : '';
    switch ($view) {
        case 'index':
            $empresas_dispo = $model_empresas->where("disponibilidad_api=1");
            include_once(__DIR__ . "/view_disponibilidad.php");
            break;
        case 'block_dispo':
            //$empresas_dispo = $model_empresas->where("disponibilidad_api=1");
            include_once(__DIR__ . "/view_block_dispo.php");
            break;
        case 'empresa':
            $clave_empresa = isset($_REQUEST['clave_empresa']) ? $_REQUEST['clave_empresa'] : null;
            if ($clave_empresa != null) {
                $empresa_dispo = $model_empresas->where("clave_empresa=:clave_empresa", array('clave_empresa' => $clave_empresa), ['*', 'id AS id_empresa']);
                if (count($empresa_dispo)) {
                    $empresa_dispo = $empresa_dispo[0];

                    $days_select = explode('|', $empresa_dispo->dias_dispo);
                    $horarios  = $model_disponibilidad->where("clave_empresa=:clave_empresa AND status=1 ORDER BY STR_TO_DATE(horario, '%h:%i %p')", array("clave_empresa" => $empresa_dispo->clave_empresa), ['horario', 'cupo', 'h_match']);
                    $info_productos = json_decode($empresa_dispo->productos, true);
                    include_once(__DIR__ . "/view_empresa_dispo.php");
                } else {
                    $empresas_dispo = $model_empresas->where("disponibilidad_api=1");
                    include_once(__DIR__ . "/view_disponibilidad.php");
                }
            } else {
                $empresas_dispo = $model_empresas->where("disponibilidad_api=1");
                include_once(__DIR__ . "/view_disponibilidad.php");
            }
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'post') {
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    switch ($action) {
        case 'add_producto_to_empresa':
            $codigo_producto = isset($_REQUEST['codigo_producto']) ? $_REQUEST['codigo_producto'] : null;
            $id_empresa = isset($_REQUEST['id_empresa']) ? $_REQUEST['id_empresa'] : null;
            $bd = isset($_REQUEST['bd']) ? $_REQUEST['bd'] : null;

            if ($codigo_producto != null &&  $id_empresa != null &&  $bd != null) {
                $empresa = $model_empresas->find($id_empresa);
                $info_productos = json_decode($empresa->productos, true);
                array_push($info_productos, array(
                    'codigoproducto' => $codigo_producto,
                    'bd' => $bd
                ));
                $model_empresas->update($id_empresa, array(
                    'productos' => json_encode($info_productos)
                ));
            }
            break;
        case 'delete_producto_to_empresa':
            $codigo_producto = isset($_REQUEST['codigo_producto']) ? $_REQUEST['codigo_producto'] : null;
            $id_empresa = isset($_REQUEST['id_empresa']) ? $_REQUEST['id_empresa'] : null;
            $bd = isset($_REQUEST['bd']) ? $_REQUEST['bd'] : null;

            if ($codigo_producto != null &&  $id_empresa != null &&  $bd != null) {
                $empresa = $model_empresas->find($id_empresa);
                $info_productos = json_decode($empresa->productos, true);

                foreach ($info_productos as $info_key => $info_producto) {
                    if ($info_producto['bd'] == $bd && $info_producto['codigoproducto'] == $codigo_producto) {
                        unset($info_productos[$info_key]);
                    }
                }

                $model_empresas->update($id_empresa, array(
                    'productos' => json_encode($info_productos)
                ));
            }
            break;
        case 'create_empresa_dispo':
            $nombre_empresa = isset($_REQUEST['nombre_empresa']) ? $_REQUEST['nombre_empresa'] : "Error Empresa";
            $clave_empresa = isset($_REQUEST['clave_empresa']) ? strtoupper($_REQUEST['clave_empresa']) : $model_empresas->getClave();
            $color_primario = isset($_REQUEST['color_primario']) ? $_REQUEST['color_primario'] : "#000";
            $dias_dispo = isset($_REQUEST['dias_dispo']) ? $_REQUEST['dias_dispo'] : [];
            $imagen_empresa = isset($_FILES['imagen_empresa']) ? $_FILES['imagen_empresa'] : null;

            $exist_empresa = $model_empresas->where("clave_empresa=:clave_empresa", array("clave_empresa" => $clave_empresa));
            if (count($exist_empresa) > 0) {
                $clave_empresa = $clave_empresa . $model_empresas->getClave();
            }

            $dias = '';

            foreach ($dias_activos as $dia => $nombre_largo) {
                if (in_array($dia, $dias_dispo)) {
                    $dias .= $dia . '|';
                }
            }
            $dias = trim($dias, '|');
            $destino = __DIR__ . '/../img/';
            $extencion_file = explode('.', $imagen_empresa['name']);
            $extencion_file = $extencion_file[count($extencion_file) - 1];
            $nuevo_nombre = $clave_empresa . '.' . $extencion_file;
            move_uploaded_file($imagen_empresa['tmp_name'], $destino . $nuevo_nombre);

            $empresa = $model_empresas->insert(array(
                'nombre' => $nombre_empresa,
                'primario' => $color_primario,
                'secundario' => '#000',
                'clave_empresa' => $clave_empresa,
                'disponibilidad_api' => 1,
                'productos' => json_encode([]),
                'dias_dispo' => $dias
            ));
            header('Location:  https://www.totalsnorkelcancun.com/dash/dispo_test/' . $clave_empresa . '/');
            break;
        case 'update_empresa_dispo':
            $id_empresa = isset($_REQUEST['id_empresa']) ? $_REQUEST['id_empresa'] : null;
            $nombre_empresa = isset($_REQUEST['nombre_empresa']) ? $_REQUEST['nombre_empresa'] : null;
            $color_primario = isset($_REQUEST['color_primario']) ? $_REQUEST['color_primario'] : null;
            $dias_dispo = isset($_REQUEST['dias_dispo']) ? $_REQUEST['dias_dispo'] : [];
            $imagen_empresa = isset($_FILES['imagen_empresa']) ? $_FILES['imagen_empresa'] : null;

            $empresa = $model_empresas->find($id_empresa);
            if (isset($empresa->clave_empresa)) {

                $dias = '';

                foreach ($dias_activos as $dia => $nombre_largo) {
                    if (in_array($dia, $dias_dispo)) {
                        $dias .= $dia . '|';
                    }
                }
                $dias = trim($dias, '|');


                if ($imagen_empresa['size'] > 0) {
                    $destino = __DIR__ . '/../img/';
                    $extencion_file = explode('.', $imagen_empresa['name']);
                    $extencion_file = $extencion_file[count($extencion_file) - 1];
                    $nuevo_nombre = $empresa->clave_empresa . '.' . $extencion_file;
                    move_uploaded_file($imagen_empresa['tmp_name'], $destino . $nuevo_nombre);
                }

                $model_empresas->update($id_empresa, array(
                    'nombre' => $nombre_empresa,
                    'primario' => $color_primario,
                    'dias_dispo' => $dias
                ));
            }
            header('Location:  https://www.totalsnorkelcancun.com/dash/dispo_test/' . $empresa->clave_empresa . '/');

            break;

        case 'change_cupo':
            $id_horario = isset($_REQUEST['id_horario']) ? $_REQUEST['id_horario'] : null;
            $new_cupo = isset($_REQUEST['new_cupo']) ? $_REQUEST['new_cupo'] : null;
            $model_disponibilidad->update($id_horario, array('cupo' => $new_cupo));
            break;
        case 'delete_horario_dispo':
            $id_horario = isset($_REQUEST['id_horario']) ? $_REQUEST['id_horario'] : null;
            $model_disponibilidad->delete($id_horario);
            break;
        case 'create_horario_empresa':
            $horario = isset($_REQUEST['horario']) ? $_REQUEST['horario'] : null;
            $cupo = isset($_REQUEST['cupo']) ? $_REQUEST['cupo'] : null;
            $clave_empresa = isset($_REQUEST['clave_empresa']) ? $_REQUEST['clave_empresa'] : null;

            $model_disponibilidad->insert(array(
                'clave_empresa' => $clave_empresa,
                'status' => 1,
                'horario' => date('h:i A', strtotime($horario)),
                'cupo' => $cupo
            ));
            break;
        case 'get_info_block_dispo':
            $fecha_dispo = isset($_REQUEST['fecha_dispo']) ? $_REQUEST['fecha_dispo'] : date("Y-m-d");
            $empr_disp = array();
            $closed_products = array();

            $dispo_block = array();
            $get_hr_bloq = $model_dispo_bloq->where("fecha_block = :fecha_block", array("fecha_block" => $fecha_dispo), ["clave_empresa", "fecha_block", "horarios"]);
            foreach ($get_hr_bloq as $d_b) {
                $dispo_block[$d_b->clave_empresa][$d_b->fecha_block] = explode("|", $d_b->horarios);
            }
            $get_emp_disp = $model_empresas->consult(
                ["E.clave_empresa", "E.nombre", "E.primario", "DP.horario"],
                "E INNER JOIN disponibilidad DP ON DP.clave_empresa = E.clave_empresa",
                "E.disponibilidad_api=1 AND DP.status=1 ORDER BY E.id,STR_TO_DATE(DP.horario, '%h:%i %p')",
                array()
            );
            foreach ($get_emp_disp as $disp) {
                $empr_disp[$disp->clave_empresa]["nombre"] = $disp->nombre;
                $empr_disp[$disp->clave_empresa]["clave_empresa"] = $disp->clave_empresa;
                $empr_disp[$disp->clave_empresa]["fecha_dispo"] = date("F j, Y", strtotime($fecha_dispo));
                $empr_disp[$disp->clave_empresa]["primario"] = $disp->primario;
                $empr_disp[$disp->clave_empresa]["img_empresa"] = "https://www.totalsnorkelcancun.com/dash/img/" . $disp->clave_empresa . ".png";
                if (!isset($empr_disp[$disp->clave_empresa]["horarios"])) $empr_disp[$disp->clave_empresa]["horarios"] = array();
                array_push($empr_disp[$disp->clave_empresa]["horarios"], array(
                    "horario" => $disp->horario,
                    "is_closed" => (isset($dispo_block[$disp->clave_empresa][$fecha_dispo]) && in_array($disp->horario, $dispo_block[$disp->clave_empresa][$fecha_dispo]))
                ));
            }
            foreach ($empr_disp as $disp) {
                array_push($closed_products, $disp);
            }
            echo json_encode($closed_products);
            //$model_empresas->where("disponibilidad_api=1");
            break;
        case 'close_horarios_dispo':
            //echo json_encode($_REQUEST);
            $clave_empresa = $_REQUEST['clave_empresa'];
            $fecha_close = date("Y-m-d", strtotime($_REQUEST['fecha_close']));
            $horarios = $_REQUEST['horarios'];
            $get_hr_bloq = $model_dispo_bloq->where("fecha_block = :fecha_block AND clave_empresa=:clave_empresa", array("fecha_block" => $fecha_close, "clave_empresa" => $clave_empresa), ["clave_empresa", "fecha_block", "horarios"]);
            $detal_close = array();
            foreach ($horarios as $horario) {
                $horario = str_replace(" ", "_", $horario);
                $horario = str_replace(":", "_", $horario);
                if (isset($_REQUEST[$horario]) && $_REQUEST[$horario] != "") $detal_close[$horario] = $_REQUEST[$horario];
            }

            if (count($get_hr_bloq) > 0) {
                $hr_bloq = $get_hr_bloq[0];
                $model_dispo_bloq->update($hr_bloq->id, array(
                    "horarios" => implode("|", $horarios),
                    "motivo_cierre" => json_encode($detal_close)
                ));
                echo json_encode($hr_bloq);
            } else {
                $hr_bloq = $model_dispo_bloq->insert(array(
                    "clave_empresa" => $clave_empresa,
                    "fecha_block" => $fecha_close,
                    "horarios" => implode("|", $horarios),
                    "motivo_cierre" => json_encode($detal_close),
                    "mensaje" => "",
                    "fk_usuario" => 146 //$_SESSION['usuario']['id_usuario']
                ));
                echo json_encode($hr_bloq);
            }
            break;
    }
}

?>

<?php
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="https://www.totalsnorkelcancun.com/dash/css/style.css">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" type="text/css" href="https://www.totalsnorkelcancun.com/dash/lib/materialize/css/materialize.min.css">
    <!--script defer type="text/javascript" src="https://www.totalsnorkelcancun.com/dash/js/jquery-3.1.1.js"></script>-->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <link rel="stylesheet" href="https://www.totalsnorkelcancun.com/booking-information/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!--<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>-->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script defer type="text/javascript" src="https://www.totalsnorkelcancun.com/dash/lib/materialize/js/materialize.js"></script>
    <script defer type="text/javascript" src="https://www.totalsnorkelcancun.com/dash/js/main.js"></script>
    <script defer type="text/javascript" src="https://www.totalsnorkelcancun.com/dash/js/fecht.js"></script>
    <title>Disponibilidad</title>
    <?php
    include_once('fragment_style.php')
    ?>
    <style>
        [type="checkbox"]:not(:checked),
        [type="checkbox"]:checked {
            display: none;
        }

        td,
        th {
            padding: 0;
        }
    </style>
</head>

<body>
    <header>
        <?php include_once(__DIR__ . "/../layout/header.php") ?>
    </header>
    <main>
        <div class="container">
            <div class="row" style="background: #f0f0f0; ">
                <div class="col s12">
                    <h4 style="font-size: 2rem;">Disponibilidad por Productos o Empresa</h4>
                </div>
                <!--div class="col s6">
                    <i class="material-icons" style="float: left;line-height: inherit;color: #FFF;background: #1976d2;padding: 5px;margin-top: 9px;">search</i>
                    <input placeholder="search" id="search_input" type="text" style="background: #FFF;border: 0px;width: 80%;float: left; margin-top: 10px;height: 44px;text-indent: 9px;">
                </div-->
            </div>

            <div class="row">
                <div class="col s2">
                    <?php
                    include_once('fragment_menu_option.php')
                    ?>
                </div>
                <div class="col s7">

                    <?php

                    //echo json_encode($empresas_dispo);

                    foreach ($empresas_dispo as $key_dispo => $empresa_dispo) {
                        $days_select = explode('|', $empresa_dispo->dias_dispo);
                        $horarios  = $model_disponibilidad->where("clave_empresa=:clave_empresa AND status=1 ORDER BY STR_TO_DATE(horario, '%h:%i %p')", array("clave_empresa" => $empresa_dispo->clave_empresa), ['horario', 'cupo']);

                        $info_productos = json_decode($empresa_dispo->productos, true);

                    ?>
                        <div class="card mb12 " style="padding: 0.5em; border: solid 1px <?php echo $empresa_dispo->primario ?>">
                            <div class="row">
                                <div class="col s12">
                                    <div class="card-title " style="display: flex; align-items: center;"><img src="https://www.totalsnorkelcancun.com/dash/img/<?php echo $empresa_dispo->clave_empresa ?>.png" alt="<?php echo $empresa_dispo->clave_empresa ?>" width="50" class="circle responsive-img"> <span><?php echo $empresa_dispo->nombre ?><a href="<?php echo $empresa_dispo->clave_empresa ?>/"></span><i class="small material-icons" style="cursor:pointer;">keyboard_arrow_right</i></a></div>
                                    <span style="font-size: small; font-weight: bold;color:green"><?php echo $empresa_dispo->clave_empresa ?></span>
                                    <div>
                                        <span style="font-weight: bold;">Dias Activos: </span>
                                        <?php
                                        $dias_ = '';
                                        foreach ($dias_activos as $name_day => $name_large) {
                                            if (in_array($name_day, $days_select)) {
                                                $dias_ .= ' ' . $name_large . ',';
                                            }
                                        }
                                        $dias_ = trim($dias_, ',');
                                        echo $dias_;
                                        ?>
                                    </div>
                                    <div><span style="font-weight: bold;">Transportacion:</span> <?php echo $empresa_dispo->transporte ? 'SI' : 'NO' ?></div>
                                </div>

                            </div>
                            <hr style="border: solid 1px <?php echo $empresa_dispo->primario ?>">

                            <div class="row ">
                                <div class="col s9 ">
                                    <label>Productos</label>

                                    <?php
                                    foreach ($info_productos as $info_producto) {
                                        $model_producto = new Productos($info_producto['bd']);
                                        $producto = $model_producto->where("codigoProducto=:codigoproducto", array("codigoproducto" => $info_producto['codigoproducto']), ['nombre', 'codigoProducto']);
                                        $producto = $producto[0];

                                    ?>
                                        <div class="card mb12 " style="padding: 0.5em; border: solid 1px <?php echo $empresa_dispo->primario ?>">
                                            <div><?php echo $producto->nombre; ?></div>
                                            <span style="font-size: small; font-weight: bold;color:green"><?php echo $producto->codigoProducto; ?></span>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col s3 ">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Horario</th>
                                                <th>Cupo</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($horarios as $horario) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $horario->horario ?></td>
                                                    <td> <span><?php echo $horario->cupo ?></span> </td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>


                </div>
                <div class="col s3">
                    <h5><i class="material-icons">business_center</i> Nueva Empresa o Producto</h5>
                    <form action="https://www.totalsnorkelcancun.com/dash/precios/controller_disponibilidad.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create_empresa_dispo">
                        <div class="input-field col s12">
                            <input name="nombre_empresa" placeholder="Nombre Empresa" id="nombre_empresa" type="text" class="validate" required>
                            <label for="nombre_empresa">Nombre Empresa</label>
                        </div>

                        <div class=" col s12" style="display: block;">
                            <span>Color de empresa</span>
                            <input name="color_primario" id="color_primario" type="color" class="validate ml1" value="#345A98" required>
                        </div>
                        <div class="input-field col s12">
                            <select multiple name="dias_dispo[]">
                                <?php
                                foreach ($dias_activos as $name_day => $name_large) {
                                ?>
                                    <option value="<?php echo $name_day ?>" selected><?php echo $name_large ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            <label>Dias activos</label>
                        </div>
                        <div class="input-field col s12 ">
                            <span>Imagen de empresa</span>
                            <input name="imagen_empresa" id="imagen_empresa" type="file" class="validate" accept="image/png" required>
                        </div>
                        <button class="waves-effect waves-light btn " style="margin-top: 1em;" type="submit"><i class="material-icons left">send</i>Enviar</button>
                    </form>


                </div>
            </div>
        </div>
    </main>
    <footer class="page-footer blue  blue darken-2">
        <?php include_once(__DIR__ . "/../layout/footer.php") ?>
    </footer>
    <script>

    </script>

</body>

</html>






<?php
include_once(__DIR__ . '/ModelTable.php');

/**Exclusivo de Total Snorkel */
class Empresa extends ModelTable
{
    function __construct()
    {
        $this->table = 'empresa';
        $this->id_table = 'id';
        parent::__construct();
    }

    public function getClave()
    {
        $cadena = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $size = strlen($cadena);
        $size--;
        $temp = '';

        for ($x = 1; $x <= 5; $x++) {
            $n = rand(0, $size);
            $temp .= substr($cadena, $n, 1);
        }
        $r = $this->where("clave_empresa = :clave_empresa", array("clave_empresa" =>  $temp));
        if (count($r) > 0) {
            $temp = $this->getClave();
        }
        return $temp;
    }
}
/**Exclusivo de Total Snorkel */
class Disponibilidad extends ModelTable
{
    function __construct()
    {
        $this->table = 'disponibilidad';
        $this->id_table = 'id_dispo';
        parent::__construct();
    }
}
class DispoBlock extends ModelTable
{
    function __construct()
    {
        $this->table = "block_dispo";
        $this->id_table = "id_d_b";
        parent::__construct();
    }
}

class Productos extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'productos';
        $this->id_table = 'id_producto';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
}

class Control extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'control';
        $this->id_table = 'idpago';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
}

class Balance extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'balance';
        $this->id_table = 'id_balance';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
}

class Bookingdetails extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'bookingdetails';
        $this->id_table = 'id_details';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }

    static function countPax($item_details = "[]")
    {
        $pax = 0;
        if ($item_details != "" && $item_details != "Array") {
            foreach (json_decode($item_details, true) as $key => $item) {
                $item = (object)$item;
                if ($item->tipo == 'tour' && $item->item > 0) {
                    if (strrpos($item->name, 'compartida') !== false || strrpos($item->name, 'Shared') !== false) {
                        $pax += $item->item * 2;
                    } else {
                        $pax += $item->item;
                    }
                }
            }
        }

        return $pax;
    }
}
class BlockDate extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = $bd == 'gama984' ? 'blockdate' : 'blockDate';
        $this->id_table = $bd == 'gama984' ? 'cBlock' : 'idfecha';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
}


class CupoReservado extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'cupo_reservado';
        $this->id_table = 'id_cupo';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
    public function getReference($lenght = 10)
    {
        $key = '';
        $pattern = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pattern) - 1;
        for ($i = 0; $i < $lenght; $i++) $key .= $pattern[mt_rand(0, $max)];
        $response =  $this->where('reference = :key', array(
            'key' => $key
        ));
        if (count($response) > 0) {
            $key = $this->getReference($lenght);
        }
        return   $key;
    }
}


class ShowSapa extends ModelTable
{
    function __construct($bd = 'parasail')
    {
        $this->table = 'showsapa';
        $this->id_table = 'id_sapa';
        $this->dbname = $bd;
        parent::__construct();
    }

    function getSapaDetail($id_reserva)
    {
        $model_sapadetail = new SapaDetail();
        $sapa_data = [];
        if ($this->dbname == 'gama984') {
            $sapa_data = $this->consult(
                ['RU.lastname as apellido', 'SS.datestamp', 'SS.foliosapa', 'RU.name as nombre', 'SS.proceso'],
                "SS INNER JOIN resuser RU ON SS.usuario = RU.cUser",
                "SS.reserva = :id_reserva",
                array("id_reserva" => $id_reserva)
            );
        } else if ($this->dbname == "parasail") {
            $sapa_data = $this->consult(
                ['RU.apellido as apellido', 'SS.datestamp', 'SS.foliosapa', 'RU.nombre as nombre', 'SS.proceso'],
                "SS INNER JOIN usuario RU ON SS.usuario = RU.id_usuario",
                "SS.reserva = :id_reserva",
                array("id_reserva" => $id_reserva)
            );
        }


        foreach ($sapa_data as  $key_sapa => $sapa) {
            $sapa_detail = $model_sapadetail->where("folio=:folio_", array('folio_' => $sapa->foliosapa));
            $sapa_data[$key_sapa]->sapas_details = $sapa_detail;
            $sapa_data[$key_sapa]->nivel = 'Reservas';
        }

        return $sapa_data; //count($sapa_data) > 0 ? [$sapa_data[count($sapa_data) - 1]] : [];
    }
}

class SapaDetail extends ModelTable
{
    function __construct()
    {
        $this->table = 'sapa_details';
        $this->id_table = 'id_service';
        $this->dbname = 'cancunrivieramaya';
        parent::__construct();
    }
}

class Sapas extends ModelTable
{
    function __construct()
    {
        $this->table = 'sapas';
        $this->id_table = 'id_sapa';
        $this->dbname = 'cancunrivieramaya';
        parent::__construct();
    }

    public function getFolio()
    {
        $ultimo_folio = $this->where("1 ORDER BY id_sapa DESC LIMIT 1", ["folio + 1 as folio"]);
        if (count($ultimo_folio) == 1) {
            return $ultimo_folio[0]->folio;
        }
    }
}


class Rep extends ModelTable
{
    function __construct($bd = '')
    {
        $this->table = 'rep';
        $this->id_table = 'idrep';
        $bd != '' ? ($this->dbname = $bd) : null;
        parent::__construct();
    }
}

class BookingMessage extends ModelTable
{
    function __construct()
    {
        $this->table = 'bookingmessage';
        $this->id_table = 'idmensaje';
        $this->dbname = 'parasail';
        parent::__construct();
    }
}

class NoteNotification extends ModelTable
{
    function __construct()
    {
        $this->table = 'notenotification';
        $this->id_table = 'id';
        $this->dbname = 'gama984';
        parent::__construct();
    }
}

class Historicaldata extends ModelTable
{
    function __construct($dbname = '')
    {
        $this->table = 'historicaldata';
        $this->id_table = 'cRecord';
        if ($dbname != '') {
            $this->dbname = $dbname;
        }
        parent::__construct();
    }

    public function getIP()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            $ip = $_SERVER["HTTP_FORWARDED"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip . ' ' . $_SERVER['HTTP_USER_AGENT'];
    }
}
