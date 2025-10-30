<?php
require_once (__DIR__ . "/../repositories/HotelRepository.php");
class HotelControllerService {
    private $hotel_repo;

    public function __construct(){
        $this->hotel_repo = new HotelRepository();
    }

    public function insert(array $data)
    {
        return $this->hotel_repo->insert($data);
    }
    public function find($id)
    {
        return $this->hotel_repo->find($id);
    }
    public function delete($id)
    {
        return $this->hotel_repo->delete($id);
    }
    public function getTableName(){
        return  $this->hotel_repo->getTableName();
    }
    public function update($id, $data){
        return  $this->hotel_repo->update($id, $data);
    }
    public function getAll(){
        return $this->hotel_repo->getAll();
    }

    public function deleteHotel(array $data, $history_service, $userData)
    {
        try {
            // Validar que venga el ID
            if (empty($data['id'])) {
                return ['status' => 400, 'message' => 'ID del hotel requerido.'];
            }

            $hotelId = intval($data['id']);

            // Buscar el hotel
            $hotel = $this->find($hotelId);
            if (!$hotel || empty($hotel->id)) {
                return ['status' => 404, 'message' => 'El hotel no existe.'];
            }

            // Eliminar hotel
            $deleted = $this->delete($hotelId);
            if (!$deleted) {
                return ['status' => 500, 'message' => 'No se pudo eliminar el hotel.'];
            }

            // Registrar historial
            $history_service->registrarHistorial(
                'hoteles',
                $hotelId,
                'delete',
                'Se eliminó un hotel',
                $userData->id ?? 0,
                $hotel,
                null
            );

            return [
                'status' => 200,
                'message' => 'Hotel eliminado correctamente.',
                'data' => $hotel
            ];

        } catch (Exception $e) {
            return [
                'status' => 500,
                'message' => 'Error al procesar la solicitud.',
                'error' => $e->getMessage()
            ];
        }
}

}