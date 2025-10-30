<?php
require_once __DIR__ . "/../repositories/BookingDetailsRepository.php";


class BookingDetailsControllerService
{
    private $bookingdetails_repo;

    public function __construct()
    {
        
        $this->bookingdetails_repo = new BookingDetailsRepository();
    }
    public function insertBookingDetails(array $data)
    {
        return $this->bookingdetails_repo->insert($data);
    }
    public function delete($id)
    {
        return $this->bookingdetails_repo->delete($id);
    }
    public function update($id, $data){
        return  $this->bookingdetails_repo->update($id, $data);
    }
    public function findByIdPago($search){
        return $this->bookingdetails_repo->findByIdPago($search);
    }
    public function getTableNameBookingDetail(){
        return  $this->bookingdetails_repo->getTableNameBookingDetail();
    }
    public function find($id){
        return  $this->bookingdetails_repo->find($id);
    }
    public function parseItemsByTipoService($items_details) {
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
    public function crearBookingDetailsService(array $data, $controlInsert, $userData) {
        $dataDetails = [
            'items_details' => $data['items_details'] ?? null,
            'idpago' => $controlInsert->id,
            'fecha_details' => $data['fecha_details'] ?? null,
            'total' => $data['total_details'] ?? null,
            'tipo' => $data['service'] ?? null,
            'usuario' => $userData->id ?? null,
            'proceso' => $data['proceso'] ?? null,
        ];

        return $this->insertBookingDetails($dataDetails);
       
    }
    public function crearBookingDetailsHijoService($bookingDetailsInsert, $idPago, $itemsFiltrados) 
    {
        $dataDetailsHijo = (array) $bookingDetailsInsert;
        $dataDetailsHijo['idpago'] = $idPago;
        $dataDetailsHijo['items_details'] = json_encode($itemsFiltrados);
        unset($dataDetailsHijo['id']);

        return $this->insertBookingDetails($dataDetailsHijo);
    }
}


