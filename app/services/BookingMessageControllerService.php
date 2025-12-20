<?php
require_once __DIR__ . '/../repositories/BookingMessageRepository.php';
class BookingMessageControllerService
{
    private $bookingmessage_repo;

    public function __construct()
    {
        $this->bookingmessage_repo = new BookingMessageRepository();
    }
    public function insert($data){
        return $this->bookingmessage_repo->insert($data);
    }
    public function find(int $id)
    {
        return $this->bookingmessage_repo->find($id);
    }
    public function delete(int $id)
    {
        return $this->bookingmessage_repo->delete($id);
    }
    public function getTableName(){
        return  $this->bookingmessage_repo->getTableName();
    }
    public function searchNotesByIdPago($search){
        return $this->bookingmessage_repo->searchNotesByIdPago($search);
    }
    public function searchLastNoteByIdPago($id){
        return $this->bookingmessage_repo->searchLastNoteByIdPago($id);
    }
    public function searchLastNoteByIdCheckin($id){
        return $this->bookingmessage_repo->searchLastNoteByIdCheckin($id);
    }
    public function searchLastNoteByIdSapa($id){
        return $this->bookingmessage_repo->searchLastNoteByIdSapa($id);
    }
    public function searchNotesByIdPagoUser($id, $user){
        return $this->bookingmessage_repo->searchNotesByIdPagoUser($id, $user);
    }
    // ---------------------------
    // Helpers
    // ---------------------------
    public function insertarMensajeReserva(array $campos, string $modulo, int $usuario_id, $history, $booking)
    {
        $response = $this->insert($campos);
        if ($response && isset($response->id)) {
            $history->registrarHistorial($modulo, $response->id, 'create', 'Se creó mensaje', $usuario_id, null, $campos);
            if (in_array($response->tipomessage, ['nota', 'importante', 'balance', 'checkin'], true)) {
                $field = $response->tipomessage == 'checkin' ? 'nota' : 'comentario';
                $oldData = $booking->find($response->idpago);
                $data = [];
                $data[$field] = $response->mensaje ?? $oldData->$field;
                $is_update = $booking->update($response->idpago, $data);
                if($is_update){
                    
                 $history->registrarHistorial("DetalleReservas", $response->id, 'update', 'Se actualizó ' . $field, $usuario_id, $oldData, $booking->find($response->idpago));
                }
            }
            
            return $response;

        }
        return null;
    }
    public function replicarMensajeEnCombos($nog, $mensaje, $usuario_id, $tipomessage, $modulo, $booking, $history)
    {
        $mensajes = [];
        $linked = $booking->getLinkedReservationsService($nog);
        if (!is_array($linked)) return [];
        foreach ($linked as $combo) {
            if (intval($combo->nog) === intval($nog)) continue;
            $campos = [
                'idpago' => $combo->id,
                'mensaje' => $mensaje,
                'usuario' => $usuario_id,
                'tipomessage' => $tipomessage
            ];
            $res = $this->insertarMensajeReserva($campos, $modulo, $usuario_id, $history, $booking);
            if ($res) $mensajes[] = $res;
        }
        return $mensajes;
    }
}


