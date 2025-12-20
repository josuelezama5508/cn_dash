<?php
require_once(__DIR__ . '/../connection/ModelTable.php');
class ControlModel extends ModelTable
{
    public function __construct()
    {
        $this->table = 'control';
        $this->id_table = 'idpago';
        $this->campos = [
            'actividad', 'code_company', 'product_id', 'datepicker', 'horario', 'tipo', 
            'cliente_name', 'statusCliente', 'cliente_lastname', 'nog', 'codepromo', 
            'telefono', 'hotel', 'habitacion', 'referencia', 'total', 'status', 
            'procesado', 'checkin', 'noshow', 'accion', 'nota', 'comentario','canal', 'balance', 'moneda', 'email',
            'metodo'
        ];
    }
}
?>
