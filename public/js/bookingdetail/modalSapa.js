// modalSapa.js
window.confirmSapa = async function () {
    const camposAValidar = [
      "cliente_nombre",
      "fecha_traslado",
      "pax_cantidad",
      "origen",
      "destino",
      "hora",
      "comentario"
    ];
  
    for (const fieldId of camposAValidar) {
      const inputEl = document.getElementById(fieldId);
      const $input = $(inputEl);
      const value = inputEl?.value || '';
      const validation = validateFieldById(fieldId, value);
  
      if (!validation.valid) {
        mostrarToastOnObject(validation.message, $input, "danger");
        inputEl?.focus();
        return;
      }
    }
  
    const data = {
      create: {
        idpago: modalData.id,
        tipo: "terrestre",
        cliente_name: document.getElementById("cliente_nombre")?.value.trim(),
        datepicker: document.getElementById("fecha_traslado")?.value.trim(),
        pax_cantidad: document.getElementById("pax_cantidad")?.value.trim(),
        origen: document.getElementById("origen")?.value.trim(),
        destino: document.getElementById("destino")?.value.trim(),
        origenV: document.getElementById("origen_vuelta")?.value.trim() || "",
        destinoV: document.getElementById("destino_vuelta")?.value.trim() || "",
        horario: document.getElementById("hora")?.value.trim(),
        nota: document.getElementById("comentario")?.value.trim(),
        traslado_tipo: document.querySelector('input[name="traslado_tipo"]:checked')?.value,
        estatus_sapa: 1,
        module: "DetalleReserva"
      }
    };
  
    try {
      const res = await fetchAPI("showsapa", "POST", data);
      const result = await res.json();
  
      if (res.ok) {
        if (result.message) showErrorModal(result);
        mostrarSapas(modalData.id);
        closeModal();
      } else {
        showErrorModal(result);
        // mostrarToastOnObject("Error: " + result.message, $("#cliente_nombre"), "danger");
      }
    } catch (err) {
      console.error(err);
      mostrarToastOnObject("Error al enviar la reserva", $("#cliente_nombre"), "danger");
    }
};
  
window.formatHora = function(horario){
    if (!horario) return '';
    const [time, modifier] = horario.split(' ');
    let [hours, minutes] = time.split(':');
    hours = parseInt(hours, 10);

    if (modifier === 'PM' && hours < 12) {
        hours += 12;
    }
    if (modifier === 'AM' && hours === 12) {
        hours = 0;
    }

    return `${hours.toString().padStart(2, '0')}:${minutes}`;
}
window.renderTransportation = function($input, $list, transports) {
    $list.empty();

    function agregarHorario($item, tr) {
        const horaLimite = formatTo24Hour(modalData.horario);

        let horariosValidos = ['tour1','tour2','tour3','tour4','tour5','nocturno','tour7']
            .map(t => ({key: t, value: tr[t]}))
            .filter(h => h.value && h.value !== '00:00:00' && h.value.trim() !== '' && h.value < horaLimite);

        if (horariosValidos.length === 0) return;

        const horarioCercano = horariosValidos.reduce((prev, curr) => {
            return curr.value > prev.value ? curr : prev;
        });

        const nombre = horarioCercano.key === 'nocturno' ? 'Nocturno' : 'Pickup';
        const horaFormateada = horarioCercano.value.slice(0,5);

        const $label = $(`<span class="badge bg-secondary me-1 mb-1">${nombre}: ${horaFormateada}</span>`);
        $item.find('.horarios').append($label);
    }

    function crearTarjeta(tr) {
        const $item = $(`
          <div class="list-group-item transport-item" data-id="${tr.id}" style="padding: 8px; position: relative; cursor: pointer;">
            <strong>${tr.hotel} - ${tr.ubicacion}</strong>
            <div class="horarios mt-1"></div>
          </div>
        `);

        $item.on('click', function () {
            $input.val(tr.hotel);
            $input.data('selected-id', tr.id);
            $list.hide();

            const $origen = $("#origen");
            if ($origen.length) $origen.val(tr.hotel || '');

            $("#hora").val(''); // Limpiar hora al seleccionar normal
        });

        return $item;
    }

    transports.forEach(tr => {
        const $item = crearTarjeta(tr);
        agregarHorario($item, tr);
        $list.append($item);
    });

    if (!$list.children().length) {
        $list.append('<div class="list-group-item text-muted">No se encontraron transportes</div>');
    }

    $list.show();
}
window.renderInitialSuggestions = function($container, suggestions) {
    $container.empty();
    $container.css({
        display: 'flex',
        overflowX: 'auto',
        gap: '12px',
        padding: '8px 0',
        borderBottom: '1px solid #ddd'
    });

    suggestions.forEach(tr => {
        const $item = $(`
            <div class="initial-suggestion-item" style="
                min-width: 180px;
                border: 2px solid #28a745;
                border-radius: 8px;
                padding: 10px;
                cursor: pointer;
                background-color: #e6ffe6;
                flex-shrink: 0;
                position: relative;
            ">
                <strong>${tr.hotel}</strong>
                <div style="font-size: 0.9em; color: #333;">${tr.ubicacion}</div>
                <div class="horarios mt-1"></div>
            </div>
        `);

        // Mostrar horario más cercano
        const horaLimite = formatTo24Hour(modalData.horario);
        let horariosValidos = ['tour1','tour2','tour3','tour4','tour5','nocturno','tour7']
            .map(t => ({key: t, value: tr[t]}))
            .filter(h => h.value && h.value !== '00:00:00' && h.value.trim() !== '' && h.value < horaLimite);

        if (horariosValidos.length) {
            const horarioCercano = horariosValidos.reduce((prev, curr) => curr.value > prev.value ? curr : prev);
            const nombre = horarioCercano.key === 'nocturno' ? 'Nocturno' : 'Pickup';
            const horaFormateada = horarioCercano.value.slice(0,5);
            const $label = $(`<span class="badge bg-success">${nombre}: ${horaFormateada}</span>`);
            $item.find('.horarios').append($label);
        }

        $item.on('click', () => {
            $("#hotelSearch").val(tr.hotel).data('selected-id', tr.id);
            $("#hotelList").hide();

            $("#origen").val(tr.hotel);
            $("#hora").val(
                horariosValidos.length ? horariosValidos.reduce((prev, curr) => curr.value > prev.value ? curr : prev).value.slice(0,5) : ''
            );
        });

        $container.append($item);
    });

    if (suggestions.length === 0) {
        $container.append('<div style="color: #666;">No hay sugerencias iniciales</div>');
    }
}

function extraerPalabraClaveIngles(texto) {
    if (!texto) return '';
    const ignorar = new Set([
        "the", "a", "an", "and", "or", "but", "from", "to", "of", "at", "in", "on", "by",
        "with", "without", "for", "no", "not", "el", "la", "los", "las", "de", "del", "y", "en", "para", "por"
    ]);
    const partes = texto.trim().split(/\s+/);
    for (let p of partes) {
        const word = p.toLowerCase();
        if (!ignorar.has(word)) {
            return word;
        }
    }
    return partes[0] || '';
}

// Inicialización si necesitas algo más al abrir modalSapa
window.initModalSapa = function(modalData) {
    if (!modalData) return;
    console.log(modalData);
    // Transporte (dejamos terrestre por defecto si no viene)
    // if (modalData.transporte_tipo) {
    //     const tipoRadio = document.getElementById(modalData.transporte_tipo);
    //     if (tipoRadio) tipoRadio.checked = true;
    // }
    const $initialSugContainer = $("#initialSuggestionsContainer");
    function calcularPaxDesdeItems(itemsRaw) {
        if (!itemsRaw) return 0;
    
        let items = [];
        try {
            items = JSON.parse(itemsRaw);
        } catch (e) {
            console.error("Error al parsear items_details:", e);
            return 0;
        }
    
        if (!Array.isArray(items)) return 0;
    
        // Sumar los "item" de tipo "tour"
        const totalPax = items
            .filter(item => item.tipo === "tour")
            .reduce((acc, curr) => acc + parseInt(curr.item || 0), 0);
    
        return totalPax;
    }
    // Fecha traslado
    document.getElementById('fecha_traslado').value = modalData.datepicker || '';

    // Cliente
    const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('cliente_nombre').value = clienteNombre;
    const correoInput = document.getElementById('correo_destino');
    if (correoInput) correoInput.value = modalData.email || '';
    // Origen y destino
    document.getElementById('origen').value = modalData.hotel || '';
    document.getElementById('destino').value = modalData.destino || 'Marina Punta Norte';
    const lang = modalData?.lang;
    if (lang === 1) {
        document.getElementById('idioma_en').checked = true;    
        document.getElementById('idioma_es').disabled = true;
    } else if (lang === 2) {
        document.getElementById('idioma_es').checked = true;
            // Deshabilitar los radios de idioma
        document.getElementById('idioma_en').disabled = true;
    }


    // Horario
    // document.getElementById('hora').value = formatHora(modalData.horario);

    // Comentario
    document.getElementById('comentario').value = modalData.comentario || '';
    const $input = $("#hotelSearch");
    const $list = $("#hotelList");
    let debounceTimer;
    $input.on("click", function () {
        // Dispara manualmente el evento input/change
        $(this).trigger("input");  
    });
    // Estilos flotantes
    $list.css({
        position: 'absolute',
        zIndex: 9999,
        maxHeight: '150px',
        overflowY: 'auto',
        width: '-webkit-fill-available',
        display: 'none', // inicia oculta
        boxShadow: '0 2px 6px rgba(0,0,0,0.2)',
        background: 'white'
    });

    // function positionList() {
    //     const offset = $input.offset();
    //     $list.css({
    //         top: offset.top + $input.outerHeight(),
    //         left: offset.left
    //     });
    // }

    // $(window).on("resize scroll", positionList);

    $input.on("input", function() {
        clearTimeout(debounceTimer);
        const query = $(this).val().trim();
        $("#initialSuggestionsContainer").hide(); // ⬅️ oculta sugerencias iniciales
        debounceTimer = setTimeout(async () => {
            let hoteles = [];
            try {
                const res = await search_transportation_tours(query, modalData.horario);
                // Solo asignar si viene un array, sino dejar vacío
                hoteles = Array.isArray(res) ? res : [];
            } catch (err) {
                console.error(err);
            }
            renderTransportation($input, $list, hoteles);
            if (hoteles.length) $list.show();
            else $list.hide();
            // positionList();
        }, 300);
    });

    $list.on("click", ".hotel-item", function() {
        const name = ($(this).data("name")).trim();
        const id = $(this).data("id");
        $input.val(name);
        $input.data("selected-id", id);
        $list.hide();
    });

    $(document).on("click", function(e) {
        if (!$(e.target).closest('#hotelSearch').length) {
            $list.hide();
        }
    });

    // === Mostrar 3 sugerencias al abrir ===
    (async function () {
        let keyword = "";
        const hotelOriginal = modalData?.hotel?.trim() || "";
    
        // 🔍 Verificar que el hotel no sea vacío ni "PENDIENTE"
        const esHotelValido = hotelOriginal && hotelOriginal.toUpperCase() !== "PENDIENTE";
    
        // 🔸 Extraer palabra clave si el hotel es válido
        if (esHotelValido) {
            keyword = extraerPalabraClaveIngles(hotelOriginal); // 👇 usamos versión extendida
        }
    
        try {
            const resultados = await search_transportation_tours(keyword, modalData.horario);
            if (!Array.isArray(resultados) || resultados.length === 0) return;
    
            const horaLimite = formatTo24Hour(modalData.horario);
    
            function horarioMasCercano(tr) {
                const horariosValidos = ['tour1', 'tour2', 'tour3', 'tour4', 'tour5', 'nocturno', 'tour7']
                    .map(t => tr[t])
                    .filter(h => h && h !== '00:00:00' && h.trim() !== '' && h < horaLimite);
    
                if (horariosValidos.length === 0) return null;
                return horariosValidos.reduce((prev, curr) => (curr > prev ? curr : prev));
            }
            const hotelesConHorario = resultados
                .map(h => ({
                    ...h,
                    horarioCercano: horarioMasCercano(h)
                }))
                .filter(h => h.horarioCercano !== null);
    
            if (hotelesConHorario.length === 0) return;
    
          // Normalizar texto a uppercase para comparación
            const normalizarTexto = txt => (txt || '').toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();

            // Buscar el hotel exacto si es válido, usando uppercase para comparar
            let hotelExacto = null;
            if (esHotelValido) {
                const ref = normalizarTexto(hotelOriginal);
                hotelExacto = hotelesConHorario.find(h => normalizarTexto(h.hotel) === ref);
            }

            let restantes = hotelesConHorario.filter(h => !hotelExacto || h.id !== hotelExacto.id);

            // Ordenar restantes por cercanía de horario
            const toMinutes = h => {
                const [hh, mm] = h.split(":").map(Number);
                return hh * 60 + mm;
            };

            restantes.sort((a, b) => toMinutes(b.horarioCercano) - toMinutes(a.horarioCercano));

            // Armar lista final poniendo primero el hotel exacto (si existe)
            const sugerenciasFinales = [
                ...(hotelExacto ? [hotelExacto] : []),
                ...restantes.slice(0, 2)
            ];

    
        renderInitialSuggestions($initialSugContainer, sugerenciasFinales);
        $initialSugContainer.show();
    
        } catch (err) {
            console.error("Error al cargar sugerencias iniciales:", err);
        }
    })();
    
    

    // ==== NUEVO: pintar datos de la empresa ====
    const logo = document.getElementById("logocompany");
    const empresaName = document.getElementById("empresaname");

    if (logo && modalData?.company_logo) {
        logo.src = modalData.company_logo;
    }

    if (empresaName && modalData?.company_name) {
        empresaName.value = modalData.company_name;
        empresaName.disabled = true; // desactiva el input
        // o si prefieres que se vea pero no se edite, solo readonly:
        // empresaName.readOnly = true;

        if (modalData?.primary_color) {
            empresaName.style.color = modalData.primary_color;
        }
    }
    // 👇 jQuery para mostrar/ocultar campos de vuelta
    function toggleCamposVuelta() {
        const tipo = $('input[name="traslado_tipo"]:checked').val();
        const $camposVuelta = $('#campos-vuelta');
    
        if (tipo === "redondo") {
        $camposVuelta.show(); // o .css("display", "flex") si lo prefieres
        } else {
        $camposVuelta.hide();
        // Limpia los valores si quieres
        $('#origen_vuelta').val('Marina Punta Norte');
        $('#destino_vuelta').val('');
        $('#destino').val('Marina Punta Norte');
        }
    }
    
    // Ejecutar al cargar el modal
    toggleCamposVuelta();
    const paxInput = document.getElementById("pax_cantidad");
    if (modalData.items_details) {
        const paxCalculado = calcularPaxDesdeItems(modalData.items_details);
        if (paxInput) {
            paxInput.value = paxCalculado;
        }
    }

    // Escuchar cambios en el tipo de traslado
    $('input[name="traslado_tipo"]').on('change', toggleCamposVuelta);
    // === Sincronizar campos de ida y vuelta ===
    function sincronizarCampos() {
        const $origen = $("#origen");
        const $destino = $("#destino");
        const $origenVuelta = $("#origen_vuelta");
        const $destinoVuelta = $("#destino_vuelta");

        function esValido(valor) {
            return valor && valor.trim() !== "" && valor.toUpperCase() !== "PENDIENTE";
        }

        // Evento ida → vuelta
        $origen.on("input", function() {
            const valor = $(this).val().trim();
            if (esValido(valor)) $destinoVuelta.val(valor);
        });

        $destino.on("input", function() {
            const valor = $(this).val().trim();
            if (esValido(valor)) $origenVuelta.val(valor);
        });

        // Evento vuelta → ida
        $origenVuelta.on("input", function() {
            const valor = $(this).val().trim();
            if (esValido(valor)) $destino.val(valor);
        });

        $destinoVuelta.on("input", function() {
            const valor = $(this).val().trim();
            if (esValido(valor)) $origen.val(valor);
        });

        // Sincronización inicial si ya viene con valores
        const origenVal = $origen.val()?.trim();
        const destinoVal = $destino.val()?.trim();

        if (esValido(origenVal)) $destinoVuelta.val(origenVal);
        if (esValido(destinoVal)) $origenVuelta.val(destinoVal);
    }

    sincronizarCampos();

}
