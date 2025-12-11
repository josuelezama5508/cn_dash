// ================== UTILS ==================

// Renderiza el estatus como icono
function stattus_widget(value) {
    if (value === "1" || value === 1) {
        return `<span style="color: green; font-size: 18px;" title="Activo">
                    <i class="material-icons">check_circle</i>
                </span>`;
    } else {
        return `<span style="color: red; font-size: 18px;" title="Inactivo">
                    <i class="material-icons">cancel</i>
                </span>`;
    }
}

// Traducción simple de lang_id
function getLangName(lang_id) {
    switch (parseInt(lang_id)) {
        case 1: return "Inglés";
        case 2: return "Español";
        case 3: return "Portugués";
        case 4: return "Alemán";
        default: return "Idioma desconocido";
    }
}

// Convertir a precio formateado
function convert_to_price(value) {
    if (!value || isNaN(value)) return "N/A";
    return `$${parseFloat(value).toFixed(2)}`;
}
// PARA DATOS TIMESTAMP
function formatFechas(datestamp) {
    const fecha = new Date(datestamp);
    const mesesCortos = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    const mesesLargos = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    const dia = fecha.getDate();
    const mesNum = fecha.getMonth();
    const año = fecha.getFullYear();
    let horas = fecha.getHours();
    let minutos = String(fecha.getMinutes()).padStart(2, "0");
    const ampm = horas >= 12 ? "PM" : "AM";
    horas = horas % 12 || 12;
    return {
        f1: `${dia} ${mesesCortos[mesNum]}, ${año} - ${horas}:${minutos} ${ampm}`,
        f2: `${dia} ${mesesLargos[mesNum]}, ${año} - ${horas}:${minutos} ${ampm}`,
        f3: `${dia} de ${mesesLargos[mesNum]} del ${año} a las ${horas}:${minutos} ${ampm}`,
        f4: `${mesesLargos[mesNum]} ${dia}, ${año} ${horas}:${minutos} ${ampm}`,
        f5: `${mesesCortos[mesNum]} ${dia}, ${año} ${horas}:${minutos} ${ampm}`
    };
}
//PARA FORMATOS DATE
function getDaySuffix(day) {
    if (day >= 11 && day <= 13) return "th";
    switch (day % 10) {
    case 1: return "st";
    case 2: return "nd";
    case 3: return "rd";
    default: return "th";
    }
}
function formatDate(dateString, lang = "en") {
    if (!dateString) return null;
    // Parse manual para evitar problemas de zona horaria
    const [y, m, d] = dateString.split("-").map(Number);
    const date = new Date(y, m - 1, d);
    if (isNaN(date)) return null;
    const day = date.getDate();
    const month = date.getMonth();
    const year = date.getFullYear();
    // Diccionarios por idioma
    const MONTHS_SHORT = {
        en: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
        es: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"]
    };

    const MONTHS_LONG = {
        en: ["January","February","March","April","May","June","July","August","September","October","November","December"],
        es: ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]
    };

    const WEEKDAYS = {
        en: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
        es: ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"]
    };
    const FIRSTCONECTOR = {
        en: ["of"],
        es: ["de"],
    }
    const SECONDCONECTOR = {
        en: ["of the"],
        es: ["del"]
    }
    // Para sufijos en inglés (1st, 2nd, etc.)
    function getDaySuffix(number) {
        if (lang === "es") return ""; // Español no usa sufijo
        if (number >= 11 && number <= 13) return "th";
        switch (number % 10) {
            case 1: return "st";
            case 2: return "nd";
            case 3: return "rd";
            default: return "th";
        }
    }
    const shortMonths = MONTHS_SHORT[lang];
    const longMonths = MONTHS_LONG[lang];
    const weekdays = WEEKDAYS[lang];
    return {
        default: dateString,
        f1: `${String(day).padStart(2,'0')}/${String(month+1).padStart(2,'0')}/${year}`,
        f2: `${String(month+1).padStart(2,'0')}/${String(day).padStart(2,'0')}/${year}`,
        f3: `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`,
        f4: `${day} ${shortMonths[month]} ${year}`,
        f5: `${day}${getDaySuffix(day)} ${shortMonths[month]} ${year}`,
        f6: `${shortMonths[month]} ${day}, ${year}`,
        f7: `${longMonths[month]} ${day}, ${year}`,
        f8: `${weekdays[date.getDay()]}, ${day} ${shortMonths[month]} ${year}`,
        f9: date.toISOString(),
        f10: `${day} ${longMonths[month]} ${year}`,
        f11: `${day} ${FIRSTCONECTOR[lang]} ${longMonths[month]} ${SECONDCONECTOR[lang]} ${year}`
    };
}
// Toast flotante
function mostrarToast(mensaje, tipo = "success") {
    const toast = $(`
        <div class="toast align-items-center text-white ${tipo==='success'?'bg-success':'bg-danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">${mensaje}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        </div>
    `);
    let container = $("#toast-container");
    if (!container.length) {
        container = $('<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>');
        $("body").append(container);
    }
    container.append(toast);
    const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
    bsToast.show();
    toast.on('hidden.bs.toast', () => toast.remove());
}
function mostrarToastMail(mensaje, tipo = "success") {
    const toast = $(`
      <div class="toast align-items-center text-white ${tipo==='success'?'bg-success':'bg-danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">${mensaje}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `);
    let container = $("#toast-container");
    if (!container.length) {
      container = $('<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>');
      $("body").append(container);
    }
    container.append(toast);
    const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
    bsToast.show();
    toast.on('hidden.bs.toast', () => toast.remove());
  }   