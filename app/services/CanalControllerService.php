<?php
require_once __DIR__ . '/../repositories/CanalRepository.php';
class CanalControllerService
{
    private $canal_repo;

    public function __construct()
    {
        $this->canal_repo = new CanalRepository();
    }
    public function insert($data){
        return $this->canal_repo->insert($data);
    }
    public function find($id){
        return $this->canal_repo->find($id);
    }
    public function delete($id){
        return $this->canal_repo->delete($id);
    }
    public function getTableName(){
        return $this->canal_repo->getTableName();
    }
    public function getAll(){
        return $this->canal_repo->getAll();
    }
    public function update($id, $data){
        return $this->canal_repo->update($id, $data);
    }
    public function getChannelById($id){
        $result = $this->canal_repo->getChannelById($id);
        return count($result) ? $result[0] : null;
    }
    public function getChannelList(){
        return $this->canal_repo->getChannelList();
    }
    public function getChannelByName($name){
        $name = strtoupper($name);
        return $this->canal_repo->getChannelByName($name);
    }
    public function getByIdActive($id){
        return $this->canal_repo->getByIdActive($id);
    }
    public function searchActives(){
        return $this->canal_repo->searchActives();
    }
    public function searchChannels($search = '')
    {
        if ($search === '') {
            return $this->searchActives();
        }
        return $this->canal_repo->searchActivesConcat($search);
    }
    public function countRepsByChannelId($idcanal, $rep_service)
    {
        $resultado = $rep_service->countReps($idcanal);
        if (count($resultado) > 0) {
            return (int) $resultado[0]->total;
        }

        return 0;
    }
    public function searchChannelService($search, $rep_service)
    {
        $channels = $this->searchChannels($search);
        error_log('searchChannels devuelve: ' . print_r($channels, true));

        foreach ($channels as $i => $row) {
            // Capitalizar tipo
            $channels[$i]->type = capitalizeString($row->type);
            // Obtener reps activos por canal
            $channels[$i]->totalreps = $this->countRepsByChannelId($row->id, $rep_service);
        }

        return $channels;
    }
    public function getChannelByIdService($search)
    {
        $channel = $this->getChannelById($search);

        if (!$channel) {
            return null;
        }

        return [
            'name' => $channel->nombre,
            'type' => $channel->tipo,
            'metodopago' => $channel->metodopago,
            'subchannel' => $channel->subCanal
        ];

    }
    public function crearCanal($data, $user, $repService, $history)
    {
        $nombre = isset($data['channelname']) ? validate_channelname($data['channelname']) : '';
        $tipo = isset($data['channeltype']) ? validate_channeltype($data['channeltype']) : '';
        $metodopago = $data['channelmethodpay'] ?? '';
        $comision = $data['channelcomision'] ?? '';

        if (!$nombre || !$tipo) {
            return ['status' => 400, 'body' => ['message' => 'Error en los datos enviados.']];
        }

        $_channel = $this->insert([
            "nombre" => $nombre,
            "tipo" => $tipo,
            "metodopago" => $metodopago,
            'comision' => $comision
        ]);

        if (!$_channel || !isset($_channel->id)) {
            return ['status' => 500, 'body' => ['message' => 'Error creando canal']];
        }

        $history->registrarHistorial(
            $data['module'] ?? 'Canales',
            $_channel->id,
            'create',
            'Creando canal',
            $user->id,
            null,
            $_channel
        );

        // Creación de reps
        $this->crearRepsAsociados($data, $_channel->id, $user, $repService, $history);

        return [
            'status' => 201,
            'body' => [
                'message' => 'El recurso fue creado con éxito.',
                'data' => $_channel
            ]
        ];
    }

    private function crearRepsAsociados($data, $idcanal, $user, $repService, $history)
    {
        $nombreArray = (array)($data['repname'] ?? []);
        $telefonoArray = (array)($data['repphone'] ?? []);
        $emailArray = (array)($data['repemail'] ?? []);
        $comisionArray = (array)($data['repcommission'] ?? []);

        if (!count($nombreArray)) return;

        for ($i = 0; $i < count($nombreArray); $i++) {
            $nombreRep = $nombreArray[$i] ?? null;
            if (empty($nombreRep)) continue;

            $_rep = $repService->insert([
                "nombre" => $nombreRep,
                "telefono" => $telefonoArray[$i] ?? null,
                "email" => $emailArray[$i] ?? null,
                "idcanal" => $idcanal,
                "comision" => $comisionArray[$i] ?? null
            ]);

            $history->registrarHistorial(
                ($data['module'] ?? 'Canales') . '_rep',
                $_rep->id,
                'create',
                'Creando rep',
                $user->id,
                null,
                $_rep
            );
        }
    }

    public function actualizarCanal($id_channel, $data, $user, $history)
    {
        $old_data = $this->getByIdActive($id_channel);
        error_log("getByIdActive($id_channel) => " . print_r($old_data, true));

        if (!count($old_data)) {
            return ['status' => 404, 'body' => ['message' => 'El recurso no existe en el servidor.']];
        }

        $old_data = $old_data[0];

        // Si solo desactiva
        if (isset($data['desactivar']) && $data['desactivar'] === true) {
            $updated = $this->update($id_channel, ["activo" => 0]);
            if ($updated) {
                $history->registrarHistorial('Canales', $id_channel, 'disable', 'Canal desactivado', $user->id, $old_data, null);
                return ['status' => 200, 'body' => ['message' => 'Canal desactivado.']];
            }
            return ['status' => 500, 'body' => ['message' => 'Error al desactivar canal.']];
        }

        // Actualización normal
        $nombre = $data['channelname'] ?? '';
        $metodopago = $data['channelmethodpay'] ?? '';
        $tipo = $data['channeltype'] ?? '';
        $subCanal = $data['subchannel'] ?? '';
        $comision = $data['channelcomision'] ?? 0;
        if (!$nombre || !$tipo) {
            return ['status' => 400, 'body' => ['message' => 'Datos incorrectos enviados en la actualización.']];
        }

        $_channel = $this->update($id_channel, [
            "nombre" => $nombre,
            "metodopago" => $metodopago,
            "tipo" => $tipo,
            "subCanal" => $subCanal,
            "comision" => $comision
        ]);

        if ($_channel) {
            $history->registrarHistorial('Canales', $id_channel, 'update', 'Actualizando canal', $user->id, $old_data, $_channel);
            return ['status' => 200, 'body' => ['message' => 'Actualización exitosa del recurso.']];
        }

        return ['status' => 500, 'body' => ['message' => 'Error en la actualización.']];
    }


}


