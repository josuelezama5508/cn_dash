<?php
require_once __DIR__ . "/../../app/core/Api.php";
require_once __DIR__ . "/../../app/core/ServiceContainer.php";
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/TemplatesMailModel.php';
require_once __DIR__ . '/../mailerservice/Mailer.php';
use App\MailerService\Mailer;
class ControlController extends API
{
    private $userModel;
    private $mailTemplate;
    private $services = [];
    private $mailer;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->mailTemplate = new TemplatesMailModel();
        $this->mailer = new Mailer();
        $serviceList = [
            'BookingControllerService',
            'TagsControllerService',
            'BookingDetailsControllerService',
            'CompanyControllerService',
            'ProductControllerService',
            'NotificationMailControllerService',
            'HistoryMailControllerService',
            'HistoryControllerService',
            'BookingMessageControllerService',
            'NotificationServiceControllerService',
            'CanalControllerService',
            'RepControllerService',
            'DisponibilidadControllerService',
            'CancellationTypesControllerService',
            'CancellationCategoriesControllerServices',
            'ComboControllerService',
            'LocationPortsControllerService',
            'EmpresaInfoControllerService',
            'IPPermissionControllerService'
        ];

        foreach ($serviceList as $service) {
            $this->services[$service] = ServiceContainer::get($service);
        }
    }
    private function service($name)
    {
        return $this->services[$name] ?? null;
    }
    private function validateToken()
    {
        $headers = getallheaders();
        $validation = $this->userModel->validateUserByToken($headers);
    
        if ($validation['status'] !== 'SUCCESS') {
            throw new Exception('NO TIENES PERMISOS PARA ACCEDER AL RECURSO', 401);
        }
        return $validation['data'];
    }
    private function resolveAction(array $params, array $map): array
    {
        foreach ($map as $key => $action) {
            if (isset($params[$key])) {
                return [$action, $params[$key]];
            }
        }
        return ['', null];
    }
    private function validateRequest(): array
    {
        $headers = getallheaders();
        $validation = $this->validateToken();
        $body = json_decode(file_get_contents("php://input"), true);
        if (!$body) {
            throw new Exception('Body JSON inválido', 400);
        }
        return [$validation, $body];
    }

    // --------------------------------------------------------
    // GET
    // --------------------------------------------------------
    public function get($params = [])
    {
        try {
            
            $user = $this->validateToken();
            [$action, $data] = $this->resolveAction($params, [
                'nog' => 'getByNog',
                'getByDispo' => 'getByDispo',
                'getByDispo2' => 'getByDispo2',
                'vinculados' => 'getLinked',
                'vinculadosByNog' => 'getCombosByNog',
                'getByDatePickup' => 'getByDatePickup',
                'searchReservation' => 'searchReservation',
                'searchReservationProcess' => 'searchReservationProcess',
                'getTagId' => 'getTagId',
                'idlocation' => 'idlocation'
                // 'getAllRep' => 'getAllRep'
            ]);

            // error_log("USUARIO GET");
            // error_log($user->productos_empresas);
            if (!$action) return $this->jsonResponse(['message' => 'Acción no reconocida'], 400);

            $booking = $this->service('BookingControllerService');
            $tag = $this->service('TagsControllerService');

            $map = [
                'getByNog' => fn() => $booking->getByNog($data),
                'getByDispo' => fn() => $booking->getByDispoBuildService($data),
                'getByDispo2' => fn() => $booking->getByDispoBuildService2($data, $this->service('ProductControllerService'), $this->service('CompanyControllerService'), $this->service('DisponibilidadControllerService')),
                'getLinked' => fn() => $booking->getLinkedReservationsService($data),
                'getCombosByNog' => fn() => $booking->getCombosByNog($data),
                'getByDatePickup' => fn() => $booking->getByDatePickupService($this->service('CanalControllerService'), $this->service('RepControllerService'), $user, $data['startdate'], $data['enddate']),
                'searchReservation' => fn() => $booking->searchReservationService($data, $user),
                'searchReservationProcess' => fn() => $booking->searchReservationProcessService($data, $user),
                'getTagId' => fn() => $tag->find($data),
                'idlocation' => fn()=> $this->service('LocationPortsControllerService')->find("8"),
                // 'getAllRep' => fn() => $this->service('CanalControllerService')->getAll()
            ];

            $result = $map[$action]();
            if($action == "searchReservationProcess"){
                // error_log("RESULTADO DE " . $action);
                // error_log(json_encode($result));
            }
            if (empty($result)) return $this->jsonResponse(['message' => 'No se encontró el recurso ' . json_encode($result)], 404);
            $resultadata = ($action === 'getByDispo2') ? $result : ['data' => $result];
            return $this->jsonResponse($resultadata, 200);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // --------------------------------------------------------
    // POST
    // --------------------------------------------------------
    public function post($params = [])
    {
        try {
            [$user, $body] = $this->validateRequest();
            [$action, $data] = $this->resolveAction($body, [
                'create' => 'create',
                'getByDate' => 'getByDate',
                'getByDatePickup' => 'getByDatePickup',
                'nog' => 'getByNog'
            ]);

            $booking = $this->service('BookingControllerService');
            switch ($action) {
                case 'create':
                    $ippermission = $this->service('IPPermissionControllerService');
                    $control = $booking->crearReservaPrincipalService($data, $ippermission);
                    if (!$control) throw new Exception('Error al crear reserva', 500);
                    $details = $this->service('BookingDetailsControllerService');
                    $history = $this->service('HistoryControllerService');
                    $historyMail = $this->service('HistoryMailControllerService');
                    $notification = $this->service('NotificationServiceControllerService');
                    $tag = $this->service('TagsControllerService');
                    $product = $this->service('ProductControllerService');
                    $company = $this->service('CompanyControllerService');
                    $empresainfo = $this->service('EmpresaInfoControllerService');
                    $notificationmail = $this->service('NotificationMailControllerService');
                    $bookingmessage = $this->service('BookingMessageControllerService');
                    $combo = $this->service('ComboControllerService');
                    $detailsInsert = $booking->validateCreateBookingDetails($details->crearBookingDetailsService($data, $control, $user));
                    $locationports = $this->service('LocationPortsControllerService');
                    $bodyMail = $booking->getByBookingDataService($control->id, $details, $product, $company, $empresainfo, $locationports);
                    // $mailResults = $booking->gestionarNotificacionCorreoService($control, $data, $user, $bodyMail, $this->mailTemplate, $historyMail, $notificationmail);

                    // $booking->enviarNotificacionService($control, $data, $notification,$company);
                    $booking->crearMensajeNotaService($control, $data, $user, $bookingmessage, $history);
                    $history->registrarHistorial('Reservas', $control->id, 'create', 'Reserva creada', $user->id, [], [$booking->getTableName() => $control, $details->getTableNameBookingDetail() => $detailsInsert] );

                    list($combosArray, $productosHijos, $productoHijoLang, $itemsTags) = $booking->crearReservasHijasService($data, $control, $detailsInsert, $user, $tag, $details, $product, $bookingmessage, $history, $combo, );

                    return $this->jsonResponse([
                        'message' => 'Reserva creada exitosamente',
                        'control' => $control,
                        'bodymail' => $bodyMail,
                        // 'correo' => $mailResults['resultadoCorreo'] ?? null,
                        'combosArray' => $combosArray,
                        'productosHijos' => $productosHijos,
                        'productoHijoLang' => $productoHijoLang,
                        'itemsTags' => $itemsTags
                    ], 200);

                case 'getByDate':
                    return $this->jsonResponse(['data' => $booking->getByDateService($data)], 200);

                case 'getByDatePickup':
                    return $this->jsonResponse(['data' => $booking->getByDatePickupService($data)], 200);

                case 'getByNog':
                    return $this->jsonResponse(['data' => $booking->getByNog($data)], 200);

                default:
                    throw new Exception('Acción POST no reconocida', 400);
            }
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // --------------------------------------------------------
    // PUT COMPLETO
    // --------------------------------------------------------
    public function put($params = [])
    {
        try {
            [$user, $body] = $this->validateRequest();
            [$action, $data] = $this->resolveAction($body, [
                'reagendar' => 'reagendar',
                'cancelar' => 'cancelar',
                'typeservice' => 'typeservice',
                'canal' => 'canal',
                'client' => 'client',
                'pax' => 'pax',
                'noshow' => 'noshow',
                'checkin' => 'checkin',
                'transporte' => 'transporte',
                'sin_transporte' => 'sin_transporte',
                'sin_email' => 'sin_email',
                'paymentmetod' => 'paymentmetod',
                'reference' => 'reference'
            ]);
            $booking = $this->service('BookingControllerService');
            $historyMail = $this->service('HistoryMailControllerService');
            $notificationMail = $this->service('NotificationMailControllerService');
            $messageService = $this->service('BookingMessageControllerService');
            $history = $this->service('HistoryControllerService');
            $details = $this->service('BookingDetailsControllerService');
            $product = $this->service('ProductControllerService');
            $company = $this->service('CompanyControllerService');
            $empresainfo = $this->service('EmpresaInfoControllerService');
            $locationports = $this->service('LocationPortsControllerService');
            if (!$action) return $this->jsonResponse(['message' => 'Acción PUT no reconocida'], 400);
            switch ($action) {
                case 'reagendar':
                case 'cancelar':                    
                    $tipo = $action === 'reagendar' ? 'Booking Reagendation' : 'Booking Cancellation';
                    $resultado = $booking->actualizarReservaConHijosService($data['idpago'], $data, $user, $tipo, "Reserva madre $action", "Reserva hijo $action", $details, $messageService, $history);
                    $bodyMail = $booking->getByBookingDataService($data['idpago'], $details, $product, $company, $empresainfo, $locationports);
                    $bodyMail['dataMail'] = $data;
                    $mailInsert = $notificationMail->insert(['nog' => $bodyMail['nog'], 'accion' => $tipo]);

                    if (!empty($mailInsert->id)) {
                        if ($tipo === "Booking Reagendation") {
                            $dataMail = $this->mailTemplate->mailReproIngFromBooking($bodyMail);
                        } else {
                            $dataCancelation = $booking->getByCancellationDataService($data['motivo_cancelacion_id'], $data['categoriaId'], $this->service('CancellationTypesControllerService'),$this->service('CancellationCategoriesControllerService'));
                            $bodyMail['dataCancellation'] = $dataCancelation;
                            $dataMail = $this->mailTemplate->mailCancelacionFromBooking($bodyMail);
                        }
                        $historyMail->registrarOActualizarHistorialCorreoService($data, $user, $dataMail);
                    }
                    if (!empty($data['descripcion'])) {
                        $msg = [
                            'idpago' => $data['idpago'],
                            'mensaje' => $data['descripcion'],
                            'usuario' => $user->id,
                            'tipomessage' => $action,
                        ];
                        $insertMsg = $messageService->insert($msg);
                        if ($insertMsg && isset($insertMsg->id)) {
                            $history->registrarHistorial('Reservas', $insertMsg->id, 'create', 'Mensaje agregado', $user->id, null, $msg);
                        }
                    }
                    return $this->jsonResponse(['message' => "Reserva $action e hijos actualizados", 'data' => $resultado], 200);
                    case 'transporte':
                    case 'sin_transporte':
                    case 'sin_email':
                        $tipo = ($action === 'transporte') 
                            ? 'Pick Up' 
                            : ($action === 'sin_transporte' ? 'Booking Notification' : 'SIN EMAIL');
                        $data['tipo'] = $tipo;
                        // Obtener datos del booking
                        $bodyMail = $booking->getByBookingDataService($data['idpago'], $details, $product, $company, $empresainfo, $locationports);
                        $bodyMail['dataMail'] = $data;
                        // error_log("bodyMail: " . print_r($bodyMail, true));
                    
                        // Insertar notificación de mail
                        $mailInsert = $notificationMail->insert([
                            'nog' => $bodyMail['nog'] ?? null,
                            'accion' => $tipo
                        ]);
                        $data['idMail'] = $mailInsert->id ?? null;
                        // error_log("mailInsert: " . print_r($mailInsert, true));
                    
                        // Procesar email si aplica
                        if (!empty($mailInsert->id) && $tipo !== "SIN EMAIL") {
                            
                            $dataMail = $this->mailTemplate->procesarReserva($bodyMail);
                            // $correoCliente = $bodyMail['email'] ?? null;   // el correo del cliente
                            // $nombreCliente = $bodyMail['cliente_name'] ?? 'Cliente'; 
                            // $correoEmpresa = $bodyMail['questions_mail'] ?? 'no-reply@miapp.com'; 
                            // $nombreEmpresa = $bodyMail['empresaname'] ?? 'Mi Empresa';
                            // $this->mailer->setFrom($correoEmpresa, $nombreEmpresa);
                            // // Enviar al cliente y a los internos
                            // $this->mailer->sendMail(
                            //     $tipo . " Confirm Notification",            // ya no importa el asunto aquí
                            //     $dataMail,
                            //     "",
                            //     [],
                            //     [$correoCliente]
                            // );
                            
                            $datahistorymail = $historyMail->registrarOActualizarHistorialCorreoService($data, $user, $dataMail);
                        }
                    
                        // Actualizar reserva y sus hijos
                        $resultado = $booking->actualizarReservaConHijosService($data['idpago'], $data, $user, $tipo, "Reserva madre $action", "Reserva hijo $action", $details, $messageService, $history);
                        return $this->jsonResponse([
                            'message' => "Reserva $action actualizada",
                            'data' => $resultado
                        ], 200);
                        break;
                        
                case 'pax':
                case 'canal':
                case 'client':
                case 'typeservice':
                case 'noshow':
                case 'checkin':
                case 'paymentmetod':
                    $resultado = $booking->actualizarReservaConHijosService($data['idpago'], $data, $user, 'update', ucfirst($action) . ' madre', ucfirst($action) . ' hijo', $details, $messageService, $history);
                    $correo = $historyMail->registrarOActualizarHistorialCorreoService($data, $user);
                    return $this->jsonResponse(['message' => "$action actualizado correctamente", 'data' => $resultado, 'correo' => $correo], 200);
                case 'reference':
                    $resultado = $booking->actualizarReservaConHijosService($data['idpago'], $data, $user, 'update', ucfirst($action) . ' madre', ucfirst($action) . ' hijo', $details, $messageService, $history);
                    $correo = $historyMail->registrarOActualizarHistorialCorreoService($data, $user);
                    return $this->jsonResponse(['message' => "$action actualizado correctamente", 'data' => $resultado, 'correo' => $correo], 200);
                default:
                    return $this->jsonResponse(['message' => 'Acción PUT no reconocida'], 400);
            }

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
