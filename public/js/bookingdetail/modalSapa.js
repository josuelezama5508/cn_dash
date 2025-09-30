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
        horario: document.getElementById("hora")?.value.trim(),
        nota: document.getElementById("comentario")?.value.trim(),
        traslado_tipo: document.querySelector('input[name="traslado_tipo"]:checked')?.value,
        proceso: "activo",
        module: "DetalleReserva"
      }
    };
  
    try {
      const res = await fetchAPI("showsapa", "POST", data);
      const result = await res.json();
  
      if (res.ok) {
        mostrarSapas(modalData.id);
        closeModal();
      } else {
        mostrarToastOnObject("Error: " + result.message, $("#cliente_nombre"), "danger");
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

    // 1️⃣ Hoteles de sugerencia buscados
    const sugerenciasNombres = ['WYNDHAM ALTRA', 'PRESIDENTE INTERCONT', 'XTEND SUITES'];
    const sugerencias = transports.filter(tr => sugerenciasNombres.includes(tr.hotel));

    function agregarHorario($item, tr) {
        ['tour1','tour2','tour3','tour4','tour5','nocturno','tour7'].forEach(t => {
            if (tr[t]) {
                // Nombre legible del horario
                let nombre = t === 'nocturno' ? 'Nocturno' : t.replace('tour','Tour ');

                // const $horaBtn = $(`
                //     <button type="button" class="btn btn-sm btn-outline-primary m-1 horario-btn">
                //         ${nombre}: ${tr[t]}
                //     </button>
                // `);

                // $horaBtn.on('click', function() {
                //     $input.val(`${tr.hotel} - ${tr[t]}`);
                //     $input.data('selected-id', tr.id);
                //     $list.hide();

                //     const $origen = $("#origen");
                //     if ($origen.length) $origen.val(tr.hotel || '');
                // });
                const $label = $(`
                        <span class="badge bg-secondary me-1 mb-1">
                            ${nombre}: ${tr[t]}
                        </span>
                    `);

                // $item.find('.horarios').append($horaBtn);
                
                $item.find('.horarios').append($label);
            }
        });
    }

    function crearTarjeta(tr, esSugerencia = false) {
        const $item = $(`
          <div class="list-group-item transport-item" data-id="${tr.id}" style="padding: 8px; position: relative; cursor: pointer;">
            <strong>${tr.hotel} - ${tr.ubicacion}</strong>
            ${esSugerencia ? '<span class="badge bg-success" style="position: absolute; top: 8px; right: 8px;">Sugerencia</span>' : ''}
            <div class="horarios mt-1"></div>
          </div>
        `);
    
        $item.on('click', function () {
          $input.val(tr.hotel);
          $input.data('selected-id', tr.id);
          $list.hide();
    
          const $origen = $("#origen");
          if ($origen.length) $origen.val(tr.hotel || '');
        });
    
        return $item;
      }
    
      // Renderizar sugerencias
      sugerencias.forEach(tr => {
        const $item = crearTarjeta(tr, true);
        agregarHorario($item, tr);
            $list.append($item);
      });
    
      // Renderizar hoteles normales
      transports
        .filter(tr => !sugerenciasNombres.includes(tr.hotel))
        .forEach(tr => {
          const $item = crearTarjeta(tr);
          agregarHorario($item, tr);
            $list.append($item);
        });

    if (!$list.children().length) {
        $list.append('<div class="list-group-item text-muted">No se encontraron transportes</div>');
    }

    $list.show();
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

    // Fecha traslado
    document.getElementById('fecha_traslado').value = modalData.datepicker || '';

    // Cliente
    const clienteNombre = `${modalData.cliente_name || ''} ${modalData.cliente_lastname || ''}`.trim();
    document.getElementById('cliente_nombre').value = clienteNombre;
    const correoInput = document.getElementById('correo_destino');
    if (correoInput) correoInput.value = modalData.email || '';
    // Origen y destino
    document.getElementById('origen').value = modalData.origen || '';
    document.getElementById('destino').value = modalData.destino || '';
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
        debounceTimer = setTimeout(async () => {
            let hoteles = [];
            try {
                const res = await search_transportation(query);
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
        const name = $(this).data("name");
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

    // Preselección si existe
    if (modalData.hotel) {
        $input.val(modalData.hotel);
    }

    // Carga inicial
    search_transportation("").then(hoteles => {
        renderTransportation($input, $list, hoteles);
        $list.hide();
        // positionList();
    });
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
}
