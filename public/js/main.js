const regexID = /^[0-9]+$/;
const regexInt = /^[0-9]+$/;
const regexProductCode = /^[A-Z]{1,16}$/;
const regexLanguageCode = /^[A-Za-z]{2,3}(-[A-Za-z]{2})?$/;
const regexPrice = /^\d{1,3}(?:,\d{3})*(?:\.\d{2})?$/;
const regexDenomination = /^[A-Z]{3}$/;
const regexTextArea = /^[^<>%$={}[\]"|`^~\\]*$/;
const regexProductType = /^(tour|store|test|season)$/;
const regexLangCode = /^[A-Za-z]{2,3}(-[A-Za-z]{2})?$/;
const regexPromoCode = /^[A-Za-z0-9\-\_]+$/;
const regexDate = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[0-9]{4}$/;
const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
const regexPhone = /^\+?[0-9]{1,4}[\s.-]?[0-9]{1,14}([\s.-]?[0-9]{1,4})?$/;
const regexCommission = /^[0-9]+$/;
const regexName = /^[A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+$/;
const regexChannelType = /^(Propio|E-Comerce|Agencia-Convencional|Bahia|Calle|Agencia\/Marina-Hotel|OTRO)$/;
const regexSubChannel = /^(directa|indirecta)$/;
const regexHexColor = /^#([A-Fa-f0-9]{6})$/;
const regexImgFile = /\.(jpg|jpeg|png|gif|webp)$/i;
const regexSchedule = /^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM|am|pm)$/;


function convert_to_price(value = '') {
    if (!isNaN(value)) {
        if (!value.toString().includes('.')) {
            return Number(value).toFixed(2);
        }
        return value.toString();
    }
    return '0.00';
}



/* ---- Comportamiento de los menus ---- */
// Boton menu horizontal
$(document).on("click", "#button-collapse", function () {
    $("#vertical-menu").css("left", "0");
    $("#overlay").css({ opacity: "1", visibility: "visible" });
});

$(document).on("click", "#overlay", function () {
    $("#vertical-menu").css("left", "-500px");
    $("#overlay").css({ opacity: "0", visibility: "hidden" });
});

// Marcar la vista actual en el menu
$(document).ready(function () {
    // var currentPath = window.location.pathname;
    var currentPath = $("[name='pagename']").val();

    function markMenu(selector) {
        $(selector + " a").each(function () {
            var linkPath = $(this).attr("href");

            if (linkPath && linkPath && currentPath) {
                if (linkPath.includes(currentPath)) {
                    $(this).addClass("active");
                    $(this).closest("ul").prev().addClass("active");
                }
            }
        });
    }

    markMenu("#horizontal-menu");
    markMenu("#vertical-menu");
    markMenu("#product-menu");
});

/* Comunicación con el API */
function DataForAPI(method, fd) {
    if (fd instanceof FormData) {
        let contieneArchivos = false;
        let json = {};
        for (let [key, value] of fd.entries()) {
            if (value instanceof File && value.name) {
                contieneArchivos = true;
            }
            if (method === "PUT" || method === "PATCH")
                contieneArchivos = false;

            let isArray = key.endsWith("[]");
            key = key.replace(/\[\]$/, "");

            if (!json[key]) {
                json[key] = isArray ? [] : "";
            }

            if (isArray) {
                json[key].push(value || "");
            } else {
                json[key] = value || "";
            }
        }
        return contieneArchivos ? fd : json;
    } else if (fd && typeof fd === "object") {
        // Si no es FormData, se asume objeto normal y se retorna tal cual
        return fd;
    }
    return null;
}

/*function DataForAPI(fd) {
    let contieneArchivos = false;
    let json = {};
        
    if (fd instanceof FormData) {
        for (let [key, value] of fd.entries()) {
            if (value instanceof File && value.name) {
                contieneArchivos = true;
            }

            let isArray = key.endsWith("[]"); // Verifica si es array
            key = key.replace(/\[\]$/, ""); // Elimina "[]" del nombre
            
            if (!json[key]) {
                json[key] = isArray ? [] : "";
            }

            if (isArray) {
                json[key].push(value || ""); // Si está vacío, guarda ""
            } else {
                json[key] = value || "";
            }
        }
    }
    
    // return json;
    // Si hay archivos, retornar el FormData original
    return contieneArchivos ? fd : json;
}*/

function fetchAPI(endpoint, method = "GET", formData = null) {
    let token = localStorage.getItem("__token");
    let bodyData = DataForAPI(method, formData);
    let options = {
        method,
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        cache: "default",
    };
    if (method !== "GET") {
        if (bodyData instanceof FormData) {
            options.body = bodyData;
            // No agregues 'Content-Type' — fetch lo hace automáticamente
        } else {
            options.body = JSON.stringify(bodyData);
            options.headers['Content-Type'] = 'application/json';
        }
    }
    const base = `${location.protocol}//${location.host}/cn_dash/api`;
    const url = `${base}/${endpoint}`;
    console.log('%c[fetchAPI] ➜', 'color: #00c853; font-weight: bold;');
    console.log('URL:', url);
    console.log('Method:', method);
    console.log('Headers:', options.headers);
    console.log('Body:', options.body instanceof FormData ? '[FormData]' : options.body);
    return fetch(url, options)
        .then(async (response) => {
            console.log('%c[fetchAPI] ⇦ RESPONSE', 'color: #2196f3; font-weight: bold;');
            console.log('Status:', response.status);
            try {
                const json = await response.clone().json();
                console.log('Response JSON:', json);
                return response;
            } catch (e) {
                console.warn('No se pudo parsear JSON de la respuesta');
                return response;
            }
        })
        .catch(error => {
            console.error('%c[fetchAPI] ✖ ERROR', 'color: red; font-weight: bold;');
            console.error(error);
            throw error;
        });
}


/*function fetchAPI(endpoint, method = "GET", formData = null) {
    let token = localStorage.getItem("__token");

    let options = {
        method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        cache: "default",
    };

    if (method !== "GET") {
        options.body = JSON.stringify(DataForAPI(formData));
    }

    return fetch(`${window.url_web}/api/${endpoint}`, options);
}*/

function fetchAPI_AJAX(endpoint, method = "GET", formData = null) {
    let token = localStorage.getItem("__token");

    let bodyData = DataForAPI(method, formData);
    let isFormData = bodyData instanceof FormData;
    let fullUrl = `${window.url_web.replace(/\/$/, '')}/api/${endpoint}`;

    // Mostrar toda la configuración antes de hacer la solicitud
    console.log('%c[fetchAPI_AJAX] ➜ PREPARANDO SOLICITUD', 'color: #00c853; font-weight: bold;');
    console.log('URL:', fullUrl);
    console.log('Method:', method);
    console.log('Headers:', {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': isFormData ? '[auto]' : 'application/json',
    });
    console.log('Body:', isFormData ? '[FormData]' : bodyData);

    // 👇 Aquí se imprime el contenido real del FormData
    if (isFormData) {
        console.log('%c[fetchAPI_AJAX] ➜ FormData CONTENT:', 'color: #ff9800; font-weight: bold;');
        for (let pair of bodyData.entries()) {
            console.log(`${pair[0]}:`, pair[1]);
        }
    }

    return $.ajax({
        url: fullUrl,
        method: method,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
        contentType: isFormData ? false : 'application/json',
        processData: !isFormData,
        dataType: 'json',
        data: method !== "GET"
            ? (isFormData ? bodyData : JSON.stringify(bodyData))
            : undefined,
        beforeSend: function () {
            console.log('%c[fetchAPI_AJAX] ➜ ENVIANDO SOLICITUD', 'color: orange; font-weight: bold;');
        },
        success: function (response, status, xhr) {
            console.log('%c[fetchAPI_AJAX] ⇦ RESPUESTA EXITOSA', 'color: #2196f3; font-weight: bold;');
            console.log('Status:', xhr.status);
            console.log('Response JSON:', response);
        },
        error: function (xhr, status, errorThrown) {
            console.error('%c[fetchAPI_AJAX] ✖ ERROR EN LA SOLICITUD', 'color: red; font-weight: bold;');
            console.error('Status:', xhr.status);
            console.error('Error:', errorThrown);
            try {
                const jsonError = JSON.parse(xhr.responseText);
                console.error('Response JSON:', jsonError);
            } catch (e) {
                console.warn('No se pudo parsear JSON del error');
                console.error('Raw Response:', xhr.responseText);
            }
        }
    });
}



/*function fetchAPI_AJAX(endpoint, method = "GET", formData = null) {
    let token = localStorage.getItem("__token");

    return $.ajax({
        url: `${window.url_web}/api/${endpoint}`,
        method: method,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
        contentType: 'application/json',
        dataType: 'json',
        data: method !== "GET" ? JSON.stringify(DataForAPI(formData)) : undefined
    });
}*/



/* ---- Validaciones de inputs ---- */
// Input codigo del producto
function sanitizeProductCode(input) { input.value = input.value.toUpperCase().replace(/[^A-Z]/g, "").substring(0, 16); }
$(document).on("input change paste", ".input-productcode", function () { sanitizeProductCode(this); });

// Input idioma
function sanitizeLanguage(input) { input.value = input.value.toUpperCase().replace(/[^A-Z]/g, "").substring(0, 2); }
$(document).on("input change paste", ".input-language", function () { sanitizeLanguage(this); });

// Input nombre del producto
function sanitizeProductName(input) { input.value = input.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]/g, ""); }
$(document).on("input change paste", ".input-productname", function () { sanitizeProductName(this); });

// Input tagname
function sanitizeTagname(input) { input.value = input.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿÁÉÍÓÚÜÑáéíóúüñÇç0-9\s\-\+\_\(\)\.,:'’\/]+/g, ""); }
$(document).on("input change paste", ".input-tagname", function () { sanitizeTagname(this); });

// Input codigo de descuento
function sanitizePromoCode(input) { input.value = input.value.toUpperCase().replace(/[^A-Za-z0-9\-\_]+/g, ""); }
$(document).on("input change paste", ".input-promocode", function () { sanitizePromoCode(this); });

function sanitizeInt(input) { input.value = input.value.replace(/[^0-9]/g, ""); }
$(document).on("input change paste", ".input-int", function () { sanitizeInt(this); });

// Input precio
$(document).on("keydown", ".input-price", function (event) {
    const input = $(this)[0];
    const key = event.key;
    let value = input.value;
    let cursorPos = input.selectionStart;

    // Permitir navegación con flechas
    if (["ArrowLeft", "ArrowRight", "Tab"].includes(key)) return;

    // Función para limpiar y formatear valor
    const formatPrice = (raw) => {
        // Eliminar todo excepto dígitos
        let digits = raw.replace(/\D/g, "");

        // Si no hay dígitos o solo son ceros
        if (!digits || /^0+$/.test(digits)) return "0.00";

        digits = digits.slice(0, 10);

        // Separar enteros y centavos
        const intPart = digits.slice(0, -2) || "0";
        const decPart = digits.slice(-2).padEnd(2, "0");

        return intPart + "." + decPart;
    };

    // Backspace personalizado
    if (key === "Backspace") {
        event.preventDefault();

        if (value === "0.00" || value.length <= 1) {
            input.value = "0.00";
            input.setSelectionRange(1, 1);
            return;
        }

        const dotPos = value.indexOf(".");

        if (cursorPos === dotPos + 1) {
            input.setSelectionRange(cursorPos - 1, cursorPos - 1);
            return;
        }

        let digits = value.replace(/\D/g, "");
        // Si después de borrar solo quedan ceros, reiniciar
        if (/^0+$/.test(digits)) {
            input.value = "0.00";
            input.setSelectionRange(1, 1);
            return;
        }
        let pos = cursorPos > dotPos ? (cursorPos - dotPos - 1) : cursorPos - 1;

        if (cursorPos <= dotPos) {
            digits = digits.slice(0, pos) + digits.slice(pos + 1);
        } else {
            digits = digits.slice(0, -1);
        }

        input.value = formatPrice(digits);
        input.setSelectionRange(Math.max(cursorPos - 1, 0), Math.max(cursorPos - 1, 0));
        return;
    }

    // Punto decimal
    if (key === ".") {
        event.preventDefault();
        const dotIndex = value.indexOf(".");
        if (dotIndex === -1) {
            input.value = value + ".00";
        }
        const intLen = input.value.indexOf(".");
        input.setSelectionRange(intLen + 1, intLen + 1);
        return;
    }

    // Validar números
    if (!/^[0-9]$/.test(key)) {
        event.preventDefault();
        return;
    }

    // Insertar número
    event.preventDefault();
    const dotPos = value.indexOf(".");
    let digits = value.replace(/\D/g, "");

    if (cursorPos <= dotPos) {
        // Estamos en la parte entera
        let intLength = digits.length - 2;
        let insertPos = cursorPos;
    
        // Si está en "0.00", reemplazar el 0 por el número
        if (value === "0.00") {
            digits = key + digits.slice(1);
            input.value = formatPrice(digits);
            input.setSelectionRange(1, 1); // mantener el cursor en enteros
            return;
        }
    
        // Insertar el número en la posición correspondiente
        digits = digits.slice(0, insertPos) + key + digits.slice(insertPos);
    
        // Limitar parte entera a 8 dígitos (ajusta según quieras)
        if (digits.length > 10) digits = digits.slice(0, 10);
    
        input.value = formatPrice(digits);
        input.setSelectionRange(cursorPos + 1, cursorPos + 1);
        return;
    } else {
        // Parte decimal
        let decimalIndex = cursorPos - dotPos - 1;
        if (decimalIndex > 1) return;
    
        digits = digits.padEnd(2, "0"); // asegurar longitud
        digits = digits.slice(0, digits.length - 2 + decimalIndex) + key + digits.slice(digits.length - 2 + decimalIndex + 1);
    
        input.value = formatPrice(digits);
        input.setSelectionRange(cursorPos + 1, cursorPos + 1);
        return;
    }

    input.value = formatPrice(digits);
    input.setSelectionRange(cursorPos + 1, cursorPos + 1);
});

// Asegurar formato correcto en input
$(document).on("input", ".input-price", function () {
    const input = $(this);
    const raw = input.val();

    if (!/\d/.test(raw) || /^0+(\.0*)?$/.test(raw)) {
        input.val("0.00");
        return;
    }

    input.val(formatPrice(raw));
});

// Validar y corregir pegado
$(document).on("paste", ".input-price", function (event) {
    event.preventDefault();
    const paste = (event.originalEvent || event).clipboardData.getData("text");
    if (!/^\d+(\.\d{1,2})?$/.test(paste)) {
        this.value = "0.00";
        this.setSelectionRange(1, 1);
    } else {
        const [intPart, decPart = ""] = paste.split(".");
        const normalized = intPart + "." + (decPart + "00").slice(0, 2);
        this.value = normalized;
        this.setSelectionRange(this.value.length, this.value.length);
    }
});

function formatPrice(raw) {
    let digits = raw.replace(/\D/g, "");

    // Si no hay dígitos, regresar el valor por defecto
    if (digits.length === 0) return "0.00";

    // Limitar longitud
    digits = digits.slice(0, 10);

    const intPart = digits.slice(0, -2) || "0";
    const decPart = digits.slice(-2).padEnd(2, "0");

    return intPart + "." + decPart;
}

// Validar checkbox
$(document).on("click", ".input-checkbox", function() {
    let isChecked = $(this).hasClass("checked"); // Detectar estado actual
    let hiddenInput = $(this).next("input[type='hidden']"); // Obtener el hidden relacionado

    if (isChecked) {
        $(this).removeClass("checked"); // Quitar estado activo
        hiddenInput.val("0"); // Guardar "0" en el hidden
    } else {
        $(this).addClass("checked"); // Activar estado
        hiddenInput.val("1"); // Guardar "1" en el hidden
    }
});



function validate_data(text, regex) {
    let ban, msg;

    if (text.length == 0) {
        ban = "vacio";
        msg = "Campo vacio.";
    } else if (!regex.test(text)) {
        ban = "invalido";
        msg = "Datos no validos.";
    } else {
        ban = "correcto";
        msg = "";
    }
    return [ban, msg];
}


function result_validate_data(input, field, ban, msg) {
    let targetTag = $(input).parent().find(`span.${field.replace("[]", "")}Error`);
    
    if (ban == "invalido") {
        $(input).css("box-shadow", "0px 0px 8px rgba(255, 0, 0, 0.6)").fadeIn("slow");
        if (targetTag.length) targetTag.css("color", "rgba(255, 0, 0");
    }
    if (ban == "vacio") {
        $(input).css("box-shadow", "0px 0px 8px rgba(255, 0, 0, 0.6)").fadeIn("slow");
        if (targetTag.length) targetTag.css("color", "rgba(255, 0, 0");
    }
    if (ban == "correcto") {}

    if (targetTag.length) targetTag.text(msg);
    setTimeout(() => {
        $(input).css("box-shadow", "none");
        if (targetTag.length) targetTag.text("");
    }, 2000);

    if (ban == "correcto") {
        return true;
    } else {
        return false;
    }
}


/* Widgets reutilizables */
function upload_screen(title, content) {
    return $.alert({
        title: title,
        content: content,
        closeIcon: false,
        buttons: false
    });
}

function stattus_widget(status = 0) {
    let color = status == 1 ? "limegreen" : "red";
    return `<i class="small material-icons" style="color: ${color}">brightness_1</i>`;
}


function createSelectLang($item) {
    let options = '';
    $item.html("<option>Conectando...</option>");

    // Obtener los idiomas ya seleccionados
    const idiomasUsados = new Set();
    $("[name='productlang[]']").each(function () {
        const val = $(this).val();
        if (val) idiomasUsados.add(val);
    });

    fetchAPI_AJAX("idiomas", "GET")
        .done((response, textStatus, jqXHR) => {
            const status = jqXHR.status;
            if (status == 200) {
                const result = response.data;
                let hayOpciones = false;
                options = '<option value="">Seleccione idioma</option>';

                result.forEach((lang) => {
                    if (!idiomasUsados.has(lang.id.toString())) {
                        options += `<option value="${lang.id}">${lang.langcode}</option>`;
                        hayOpciones = true;
                    }
                });

                if (!hayOpciones) {
                    options = '<option value="">Sin idiomas disponibles</option>';
                }

                $item.html(options);
            }
        })
        .fail((error) => {
            $item.html('<option>Error al cargar</option>');
        });
}


function createSelectPrice($item) {
    let options = "";

    $item.html("<option>Connecting...</option>");

    fetchAPI_AJAX("precios", "GET")
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 200) {
            const result = response.data;
            result.forEach((lang) => {
                options += `<option value="${lang.price}">${lang.price}</option>`;
            });
            $item.html(options);
        }
      })
      .fail((error) => {});

    return options;
}

function createSelectDenom($item) {
    let options = "";

    $item.html("<option>Connecting...</option>");

    fetchAPI_AJAX("denominaciones", "GET")
      .done((response, textStatus, jqXHR) => {
        const status = jqXHR.status;
        if (status == 200) {
            const result = response.data;
            result.forEach((lang) => {
                options += `<option value="${lang.id}">${lang.denomination}</option>`;
            });
            $item.html(options);
        }
      })
      .fail((error) => {});

    return options;
}