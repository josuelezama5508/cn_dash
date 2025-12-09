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
    public function insertarMensajeReserva(array $campos, string $modulo, int $usuario_id, $history)
    {
        $response = $this->insert($campos);
        if ($response && isset($response->id)) {
            $history->registrarHistorial($modulo, $response->id, 'create', 'Se creó mensaje', $usuario_id, null, $campos);
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
            if (intval($combo->id) === intval($nog)) continue;
            $campos = [
                'idpago' => $combo->id,
                'mensaje' => $mensaje,
                'usuario' => $usuario_id,
                'tipomessage' => $tipomessage
            ];
            $res = $this->insertarMensajeReserva($campos, $modulo, $usuario_id, $history);
            if ($res) $mensajes[] = $res;
        }
        return $mensajes;
    }
}


