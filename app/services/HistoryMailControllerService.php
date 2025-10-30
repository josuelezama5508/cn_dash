<?php
require_once __DIR__ . '/../repositories/HistoryMailRepository.php';

class HistoryMailControllerService
{
    private $historymail_repo;

    public function __construct()
    {
        $this->historymail_repo = new HistoryMailRepository();
    }
    public function getTableName()
    {
        return $this->historymail_repo->getTableName();
    }
    public function find($id){
        return $this->historymail_repo->find($id);
    }
    public function delete($id){
        return $this->historymail_repo->delete($id);
    }
    public function update($id, $data){
        return $this->historymail_repo->update($id, $data);
    }
    public function insert(array $data){
        return $this->historymail_repo->insert($data);
    }
    public function getHistoryByIdRowAndModuleAndType($id, $module, $action){
        return $this->historymail_repo->getHistoryByIdRowAndModuleAndType($id, $module, $action);
    }
    public function getByRowId($id)
    {
        return $this->historymail_repo->getByRowId($id);
    }
    public function getByRowIdAndAction($id, $action)
    {
        return $this->historymail_repo->getByRowIdAndAction($id, $action);
    }
    public function getMailByNog($search, $booking_service)
    {   
        $dataFBI = json_decode($search, true);
        $dataControl = $booking_service->getByNogV2($dataFBI['nog']);
        return $this->getByRowIdAndAction($dataControl[0]->id, $dataFBI['accion']);
    }
    public function registrarHistorialCorreoService($module, $row_id, $action, $details, $user_id, $oldData, $newData)
    {
        $this->historymail_repo->insert([
            "module" => $module,
            "row_id" => $row_id,
            "action" => $action,
            "details" => $details,
            "user_id" => $user_id,
            "old_data" => json_encode($oldData),
            "new_data" => json_encode($newData),
        ]);
    }

    public function registrarOActualizarHistorialCorreoService($data, $userData, $bodyHTML = '')
    {
        $tipo = $data['tipo'] ?? $data['function'] ?? 'tipo-desconocido';
        $correo = $data['correo'] ?? 'desconocido';
        $destinatario = $data['destinatario'] ?? 'usuario';

        $newLog = [
            'fecha' => time(),
            'idMail' => $data['idMail'] ?? null,
            'correoMail' => '',
            'destino' => '',
            'destinatario' => '',
            'body' => "$bodyHTML" ?: "<p>Simulación de correo para tipo <strong>{$tipo}</strong></p>",
            'user' => $userData->id ?? null,
            'title' => '',
        ];
        $oldDataDecoded = [];
        $historialExistente = $this->getHistoryByIdRowAndModuleAndType(
            $data['idpago'] ?? null,
            $data['module'] ?? null,
            $data['tipo'] ?? null
        );
        if (count($historialExistente)) {
            $historial = $historialExistente[0];
            $historialId = $historial->id ?? null;
            $oldDataDecoded = json_decode($historial->old_data ?? '[]', true);
            $prevNewDataDecoded = json_decode($historial->new_data ?? '{}', true);

            if (!empty($prevNewDataDecoded)) {
                $oldDataDecoded[] = $prevNewDataDecoded;
               
            }

            $this->historymail_repo->update($historialId, [
                'old_data' => json_encode($oldDataDecoded),
                'new_data' => json_encode($newLog)
            ]);

        } else {
            $this->registrarHistorialCorreoService(
                $data['module'] ?? null,
                $data['idpago'] ?? null,
                $data['tipo'] ?? null,
                "Envio de '{$tipo}'",
                $userData->id ?? null,
                [],
                $newLog
            );
        }

        return [
            'message' => "Correo Enviado",
            'oldData' => $oldDataDecoded ?? [],
            'newData' => $newLog
        ];
    }
}
