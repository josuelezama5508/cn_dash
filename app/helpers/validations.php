<?php
// --- Validaciones generales ---
function validate_username($value) {
    $regex = "/^[A-Za-z0-9\s]*\(?[A-Za-z0-9\s]*\)?[A-Za-z0-9\s]*$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_password($value) {
    $regex = "/^[A-Za-z0-9._-]+$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_id($value) {
    $regex = "/^[0-9]+$/";
    return (preg_match($regex, $value ?? '') ? intval($value) : 0);
}

function validate_int($value) {
    $regex = "/^[0-9]+$/";
    return (preg_match($regex, $value ?? '') ? intval($value) : 0);
}

function validate_productcode($value) {
    $regex = "/^[A-Z]{1,16}$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_producttype($value) {
    $regex = "/^(tour|store|test|season)$/";
    return (preg_match($regex, $value ?? '') ? $value : 'tour');
}

function validate_language($value) {
    $regex = "/^[A-Za-z]{2,3}(-[A-Za-z]{2})?$/";
    return (preg_match($regex, $value ?? '') ? strtoupper($value) : 'EN');
}

function validate_productname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_price($value) {
    $regex = "/^\d{1,3}(?:,\d{3})*(?:\.\d{2})?$/";
    return (preg_match($regex, $value ?? '') ? convert_to_price($value) : '0.00');
}

function validate_denomination($value) {
    $regex = "/^[A-Z]{3}$/";
    return (preg_match($regex, $value ?? '') ? strtoupper($value) : 'USD');
}

function validate_textarea($value) {
    $regex = "/^[^<>%$={}\\[\\]\"|`^~\\\\]*$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_status($value) {
    $status = array(0, 1);
    return (in_array(intval($value ?? 0), $status) ? intval($value) : 0);
}

function validate_tagname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_producttagtype($value) {
    $regex = "/^(tour|addon|extraquestion|store)$/";
    return (preg_match($regex, $value ?? '') ? $value : 'tour');
}

function validate_producttagclass($value) {
    $regex = "/^(number|checkbox)$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_date($value) {
    $regex = "/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[0-9]{4}$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_channelname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/&]+$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_channeltype($value) {
    $regex = "/^(Propio|E-Comerce|Agencia-Convencional|Bahia|Calle|Agencia\/Marina-Hotel|OTRO)$/";
    return (preg_match($regex, $value ?? '') ? $value : 'Agencia-Convencional');
}

function validate_phone($value) {
    $regex = "/^\+?[0-9]{1,4}[\s.-]?[0-9]{1,14}([\s.-]?[0-9]{1,4})?$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_phone_rep($value) {
    $regex = '/^(\+?\d{1,4}(?:\s*\(\d+\))?(?:[\s.\-]*\d{1,4}){1,6})(?:\s*\|\s*\+?\d{1,4}(?:\s*\(\d+\))?(?:[\s.\-]*\d{1,4}){1,6})*$/';
    return (preg_match($regex, $value ?? '') ? $value : '');
}


function validate_repname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_email($value) {
    $regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_subchannel($value) {
    $regex = "/^(directa|indirecta)$/";
    return (preg_match($regex, $value ?? '') ? $value : 'indirecta');
}

function validate_companyname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}

function validate_hexcolor($value) {
    $regex = "/^#([A-Fa-f0-9]{6})$/";
    return (preg_match($regex, $value ?? '') ? strtoupper($value) : '');
}

function validate_schedule($value) {
    $regex = "/^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM|am|pm)$/";
    return (preg_match($regex, $value ?? '') ? $value : '');
}


// --- Funciones de conversión ---
function convert_to_price($value) {
    if ($value === null || $value === '') return "0.00";
    $value = str_replace(",", "", (string)$value);
    return number_format((float)$value, 2, '.', '');
}

function convert_to_int($value) {
    if ($value === null || $value === '') return 0;
    return intval($value);
}

function convert_to_string($value) {
    if ($value === null) return '';
    return (string)$value;
}

function convert_to_bool($value) {
    if ($value === null) return false;
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function convert_to_date($value) {
    if ($value === null || $value === '') return null;
    $timestamp = strtotime($value);
    return $timestamp ? date("Y-m-d", $timestamp) : null;
}
/**
 * Obtiene un valor seguro de un array sin warnings/notices.
 *
 * @param array  $array   El array donde buscar.
 * @param mixed  $key     La clave a buscar.
 * @param mixed  $default Valor por defecto si no existe la clave.
 * @return mixed
 */
function safe_array_get($array, $key, $default = null) {
    return (is_array($array) && array_key_exists($key, $array)) ? $array[$key] : $default;
}

/**
 * Obtiene un índice de un array numérico sin warnings.
 *
 * @param array $array
 * @param int   $index
 * @param mixed $default
 * @return mixed
 */
function safe_array_index($array, $index, $default = null) {
    return (is_array($array) && isset($array[$index])) ? $array[$index] : $default;
}
// --- Funciones adicionales ---

function capitalizePreservingSeparators($str) {
    return preg_replace_callback('/\b[a-z]/', function ($match) {
        return strtoupper($match[0]);
    }, $str);
}

function capitalizeString($str) {
    return preg_replace_callback('/\b[a-z]/', function ($match) {
        return strtoupper($match[0]);
    }, $str);
}

function date_format_for_the_database($date) {
    $date = str_replace('/', '-', $date);
    return date('Y-m-d', strtotime($date));
}

function date_format_for_the_view($date) {
    return date('d/m/Y', strtotime($date));
}
