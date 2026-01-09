<?php
require_once __DIR__ . '/app/core/ServiceContainer.php';

$lang = 'es';
$tipo = 'pagar';
$data = [
  "leng" => 'es',
  "primary_color" => '#8B27F5',
  "website" => "http://www.cochinotes.com",
  "webname" => "cochinotes.com"
];
$company_service        = ServiceContainer::get('CompanyControllerService');
$languagecodes_service  = ServiceContainer::get('LanguageCodesControllerService');
$prices_service         = ServiceContainer::get('PricesControllerService');
$currencycodes_service  = ServiceContainer::get('CurrencyCodesControllerService');
$product_service        = ServiceContainer::get('ProductControllerService');
$canal_service          = ServiceContainer::get('CanalControllerService'); 
$rep_service            = ServiceContainer::get('RepControllerService'); 
$estatussapa_service    = ServiceContainer::get('EstatusSapaControllerService');
$rol_service            = ServiceContainer::get('RolControllerService');
$user_service           = ServiceContainer::get('UserControllerService');
$booking_service        = ServiceContainer::get('BookingControllerService');

// Generar el HTML del correo
$html = $booking_service->getSapaByIdPagoActive(20);

// Mostrar el HTML generado (para pruebas)
echo '<pre>';
print_r($html);
echo '</pre>';

// Luego usar $html para enviarlo con tu sistema SMTP o PHPMailer
?>
