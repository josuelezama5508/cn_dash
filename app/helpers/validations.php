<?php
function convert_to_price($value = '') {
    if (is_numeric($value)) {
        if (strpos($value, '.') === false) {
            return number_format($value, 2, '.', '');
        }
        return $value;
    }
    return '0.00';
}

function validate_username($value) {
    $regex = "/^[A-Za-z0-9\s]*\(?[A-Za-z0-9\s]*\)?[A-Za-z0-9\s]*$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_password($value) {
    $regex = "/^[A-Za-z0-9._-]+$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_id($value) {
    $regex = "/^[0-9]+$/";
    return (preg_match($regex, $value)) ? intval($value) : 0;
}

function validate_int($value) {
    $regex = "/^[0-9]+$/";
    return (preg_match($regex, $value)) ? intval($value) : 0;
}

function validate_productcode($value) {
    $regex = "/^[A-Z]{1,16}$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_producttype($value) {
    $regex = "/^(tour|store|test|season)$/";
    return (preg_match($regex, $value)) ? $value : 'tour';
}

function validate_language($value) {
    $regex = "/^[A-Za-z]{2,3}(-[A-Za-z]{2})?$/";
    return (preg_match($regex, $value)) ? strtoupper($value) : 'EN';
}

function validate_productname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_price($value) {
    $regex = "/^\d{1,3}(?:,\d{3})*(?:\.\d{2})?$/";
    return (preg_match($regex, $value)) ? convert_to_price($value) : '0.00';
}

function validate_denomination($value) {
    $regex = "/^[A-Z]{3}$/";
    return (preg_match($regex, $value)) ? strtoupper($value) : 'USD';
}

function validate_textarea($value) {
    $regex = "/^[^<>%$={}\\[\\]\"|`^~\\\\]*$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_status($value) {
    $status = array(0, 1);
    return (in_array(intval($value), $status)) ? intval($value) : 0;
}

function validate_tagname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_producttagtype($value) {
    $regex = "/^(tour|addon|extraquestion|store)$/";
    return (preg_match($regex, $value)) ? $value : 'tour';
}

function validate_producttagclass($value) {
    $regex = "/^(number|checkbox)$/";
    return (preg_match($regex, $value)) ? $value : '';
}


function validate_date($value) {
    $regex = "/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[0-9]{4}$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function date_format_for_the_database($date) {
    $date = str_replace('/', '-', $date);
    return date('Y-m-d', strtotime($date));
}

function date_format_for_the_view($date) {
    return date('d/m/Y', strtotime($date));
}

function validate_channelname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_channeltype($value) {
    $regex = "/^(Propio|E-Comerce|Agencia-Convencional|Bahia|Calle|Agencia\/Marina-Hotel|OTRO)$/";
    return (preg_match($regex, $value)) ? $value : 'Agencia-Convencional';
}

function validate_phone($value) {
    $regex = "/^\+?[0-9]{1,4}[\s.-]?[0-9]{1,14}([\s.-]?[0-9]{1,4})?$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_repname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_email($value) {
    $regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_subchannel($value) {
    $regex = "/^(directa|indirecta)$/";
    return (preg_match($regex, $value)) ? $value : 'indirecta';
}

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

function validate_companyname($value) {
    $regex = "/^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/";
    return (preg_match($regex, $value)) ? $value : '';
}

function validate_hexcolor($value) {
    $regex = "/^#([A-Fa-f0-9]{6})$/";
    return (preg_match($regex, $value)) ? strtoupper($value) : '';
}

function validate_schedule($value) {
    $regex = "/^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM|am|pm)$/";
    return (preg_match($regex, $value)) ? $value : '';
}