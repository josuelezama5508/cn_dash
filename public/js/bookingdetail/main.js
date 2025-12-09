let descuento = 0;
let porcentajeDescuento = 0; // ✅ ahora es global
let fechaOriginal = "";
let sapaData = "";
$(document).ready(async function () {
  const pathParts = window.location.pathname.split('/').filter(Boolean); // Quita vacíos
  const nog = pathParts[3]; // [0]=cn_dash, [1]=detalles-reserva, [2]=view, [3]=1MIMBR45MQ
  
  let descuentoAplicado = 0;
  console.log("NOG desde path:", nog);
  if (!nog) {
    alert("No se proporcionó el parámetro NOG.");
    return;
  }
  try {
    const response = await fetchAPI(`control?nog=${encodeURIComponent(nog)}`, 'GET');
    const json = await response.json();

    if (!json?.data?.length) {
      alert("No se encontró la reserva.");
      return;
    }
    const reserva = json.data[0];
    modalData = reserva;
    console.log("Reserva completa:", reserva);
    if (reserva.codepromo?.trim()) {
      const endpoint = `promocode?codecompany=${encodeURIComponent(reserva.code_company)}&codepromo=${encodeURIComponent(reserva.codepromo)}`;
      try {
        const promoResponse = await fetchAPI(endpoint, 'GET');
        const promoData = await promoResponse.json();

        if (promoResponse.ok && promoData.data?.length) {
          const descuento = parseFloat(promoData.data[0].descount) / 100;
          if (!isNaN(descuento)) {
            descuentoAplicado = 1 - descuento;
            porcentajeDescuento = (descuento * 100).toFixed(0); // <- aquí también actualizas global
          }else {
            alert("Descuento inválido recibido.");
          }
        } else {
          alert("Error: " + (promoData.message || "Código inválido."));
        }
      } catch (error) {
        console.error("Error al validar promoción:", error);
        alert("Error en la conexión. Intenta de nuevo más tarde.");
      } finally {
        $('#btnCanjearPromo').prop('disabled', false).text('Canjear');
      }
    }
    if (modalData.id) {
      if(window.userInfo.level != "checkin"){
        renderUltimoMensajeContent(modalData.id);
      }
      if(window.userInfo.level != "reservaciones"){
        autofillCheckin(modalData.id);
      }
      mostrarSapas(modalData.id, modalData);
    }
    // Botón cerrar modal
    $('#btnCerrarModalCorreos').on('click', cerrarModalCorreos);

    // Ejemplo: abrir modal cuando se presione un botón específico
    $('#btnAbrirCorreos').on('click', abrirModalCorreos);
    function getPorcentajeDescuento() {
      return ((1 - descuentoAplicado) * 100).toFixed(0);
    }
    
    const items = JSON.parse(reserva.items_details || "[]");
    function esEstatusNoPermitidoCancelado(id_estatus) {
      // Define aquí los estatus que NO deben permitir editar pax
      const noPermitidos = ["2"];
      return noPermitidos.includes(String(id_estatus));
    }
    function esEstatusNoPermitidoPagar(id_estatus) {
      // Define aquí los estatus que NO deben permitir editar pax
      const noPermitidos = ["1", "0"];
      return noPermitidos.includes(String(id_estatus));
    }
    function controlarBotonEditarPax(reserva) {
      const items = JSON.parse(reserva.items_details || "[]");
      const esCombo = items.length > 0 && items.every(item => parseFloat(item.price) === 0);
    
      if (esCombo ) {
        $("#btnEditarPax").hide();
      } else {
        $("#btnEditarPax").show();
      }
    }
    if(esEstatusNoPermitidoPagar(reserva.id_estatus)){
      $("#btn_pagar").hide();
    }else{
      $("#btn_pagar").show();
    }
    if(esEstatusNoPermitidoCancelado(reserva.id_estatus)){
      $("#btnCancelarReserva").hide();
    }else{
      $("#btnCancelarReserva").show();
    }

    // Función para renderizar datos de reserva e items
    async function autofillCheckin(id) {
      const messageCheckin = await search_last_messages_checkin(modalData.id);
      const mensaje =Array.isArray(messageCheckin) && messageCheckin.length > 0 && messageCheckin[0]?.mensaje?messageCheckin[0].mensaje: '';
      $('#comentario_checkin').val(mensaje);
    }
    
    async function renderReserva() {
      // Datos usuario
      camposUsuario.nombre.val(usuarioOriginal.nombre);
      camposUsuario.apellido.val(usuarioOriginal.apellido);
      camposUsuario.email.val(usuarioOriginal.email);
      camposUsuario.telefono.val(usuarioOriginal.telefono);
      camposUsuario.hotel.val(usuarioOriginal.hotel);
      camposUsuario.habitacion.val(usuarioOriginal.habitacion);

      // Datos reserva
      $("#reserva_actividad").text(reserva.actividad ?? 'N/A');
      // Cambiar el texto o contenido si quieres mostrarlo en otro lado, 
      // pero para la imagen se cambia el atributo src
      $("#company_logo_home").attr("src", window.url_web + reserva.company_logo);
      
      // $("#company_name").text(reserva.company_name ?? 'N/A');
      fechaOriginal = formatDate(reserva.datepicker, "es");
      if (fechaOriginal) {
        $("#reserva_fecha").text(fechaOriginal.f10);
      } else {
        $("#reserva_fecha").text('N/A');
      }

      $("#reserva_hora").text(reserva.horario ?? 'N/A');
      $("#reserva_booking").text(reserva.nog ?? 'N/A');

      // Obtener los ítems seleccionados guardados
      const selectedItems = JSON.parse(reserva.items_details || "[]");
      const selectedMap = {};
      selectedItems.forEach(item => {
        const ref = item.reference?.trim();
        if (ref) {
          selectedMap[ref] = item;
        }
      });

      // Traer los items base desde backend para tener precios y moneda actualizados
      let itemsBase = [];
      try {
        itemsBase = await fetch_items(reserva.product_code);
      } catch (err) {
        console.error("Error al fetch_items para productos:", err);
        // En caso de error, fallback: render los que ya tienes
        const fallbackHtml = selectedItems.map(item => `
          <tr>
            <td>${item.item}</td>
            <td>${item.name}</td>
            <td>$${parseFloat(item.price).toFixed(2)} ${reserva.moneda}</td>
          </tr>
        `).join('');
        $("#reserva_items").html(fallbackHtml);
        return;
      }

      // Cruzar lógica: para cada item seleccionado, obtener su precio/moneda del base
      const mergedItems = Object.values(selectedMap).map(sel => {
        const ref = sel.reference?.trim();
        const base = itemsBase.find(b => b.reference?.trim() === ref);

        return {
          item: sel.item,
          name: sel.name,
          price: base ? parseFloat(base.price).toFixed(2) : parseFloat(sel.price).toFixed(2),
          moneda: base?.moneda || reserva.moneda || 'USD'
        };
      });

      // Control de botón editar Pax
      const esCombo = mergedItems.length > 0 && mergedItems.every(mi => parseFloat(mi.price) === 0);
      if (esCombo) {
        $("#btnEditarPax").hide();
      } else {
        $("#btnEditarPax").show();
      }

      // Renderizar la tabla con los datos cruzados
      const itemsHtml = mergedItems.map(mi => `
        <tr>
          <td>${mi.item}</td>
          <td>${mi.name}</td>
          <td>$${mi.price} ${mi.moneda}</td>
        </tr>
      `).join('');
      $("#reserva_items").html(itemsHtml);

      // Otros datos
      $("#pago_referencia").text(reserva.referencia ?? '');
      $("#reserva_balance").text(`${(reserva.id_estatus === 1) ? "Sin balance" :`$${parseFloat(reserva.balance).toFixed(2)} USD`}`);
      if (reserva.id_estatus === 2) {
        $('#motivo_cancelación').text(reserva.accion || '');
        // Quita la clase d-none si la tiene
        $('#motivo_cancelación').closest('.mb-3')
          .removeClass('d-none')
          .show(); // También aplica display:block
      } else {
        $('#motivo_cancelación').closest('.mb-3')
          .addClass('d-none')
          .hide();
      }
      
      $("#reserva_checkin").text("No").addClass("badge bg-danger");
      if(reserva.checkin == '0'){
        $("#web_checkin").text('FALSE').addClass('badge bg-danger text-white');
      }else{
        $("#web_checkin").text('TRUE').addClass('badge background-green-custom text-white');
      }
      
      $("#reserva_canal").text(reserva.canal_nombre ?? "N/A");
      $("#reserva_rep").text(reserva.rep_nombre ?? "N/A");
      $("#reserva_tipo").text(reserva.type ?? "NORMAL");
      // $('#pago_estado').text((reserva.proceso ?? "PENDIENTE").toUpperCase());
      $('#reserva_referencia').text((reserva.referencia ?? "N/A").toUpperCase());
      $("#reserva_fecha_compra").text(formatearFecha(reserva.fecha_details) ?? 'N/A');
      $("#reserva_metodo_pago").text((reserva.metodo ?? 'balance').toUpperCase());
      $("#reserva_ip").text("N/A");
      $("#reserva_nav").text("Undefined");
      $("#reserva_os").text("Undefined");
      $("#reserva_noshow").prop("checked", false);

      // Estado visual
      const estado = (reserva.status ?? "SIN ESTADO").toUpperCase();
      const procesadoValor = reserva.procesado ?? false;
      const procesadoBadge = $("#reserva_procesado");

      if (procesadoValor === true || procesadoValor === 1 || procesadoValor === "SI" || procesadoValor === "si") {
        procesadoBadge.text("PROCESADO");
        procesadoBadge.removeClass().addClass("badge background-green-custom-3 p-2 text-white w-100 d-block text-start  fw-normal fs-15-px rounded-1");
      } else {
        procesadoBadge.text("NO PROCESADO");
        procesadoBadge.removeClass().addClass("badge bg-danger p-2 text-white w-100 d-block text-start fw-normal fs-15-px rounded-1");
      }
      // check-in switch
      // const checkinSwitch = $("#reserva_checkin_switch");
      // const isCheckin = (reserva.checkin === true || reserva.checkin === 1 || reserva.checkin === "SI" || reserva.checkin === "si");

      // checkinSwitch.prop("checked", isCheckin);
      const checkin = Number(reserva.checkin);
      const noshow = Number(reserva.noshow);
      const statusColor = reserva.statuscolor || null;
      
      const container = $("#reserva_estado");
      container.empty(); // Limpiar contenido previo
      
      // Función para crear <p> con texto, clase y color personalizado
      function crearElemento(texto, clase = "", color = null) {
        const estilo = color ? `style="background-color: ${color} !important;"` : "";
        return `<p class="text-uppercase fs-15-px fw-normal text-white ${clase} ms-0 me-1 px-2 py-1 rounded-1 fs-7" ${estilo}>${texto}</p>`;
      }
      
      // Crear contenedor base
      let contenido = `<div class="d-flex p-0 m-0 justify-content-start align-items-start flex-wrap">`;
      
      // === ESTADOS PRINCIPALES ===
      switch (estado) {
        case "PAGADO":
          contenido += crearElemento("Pagado", "bg-success", statusColor);
          break;
        case "CANCELADO":
          contenido += crearElemento("Cancelado", "bg-cancelado", statusColor);
          break;
        case "BALANCE":
          contenido += crearElemento("Balance", "bg-balance", statusColor);
          break;
        case "PROSPECTO":
          contenido += crearElemento("Prospecto", "bg-prospecto", statusColor);
          break;
        case "PROCESADO":
          // No aplicar statusColor si es PROCESADO
          contenido += crearElemento("Procesado", "text-success");
          break;
        default:
          contenido += crearElemento(estado, "text-danger", statusColor);
          break;
      }
      
      // === CHECKIN / NOSHOW INDEPENDIENTES ===
      if (checkin === 1) {
        contenido += crearElemento("Check in", "bg-checkin");
      }
      if (noshow === 1) {
        contenido += crearElemento("No Show", "bg-noshow");
      }
      
      contenido += `</div>`;
      
      // Agregar al DOM
      container.append(contenido);
      

      // Mostrar promo si aplica
      if (reserva.codepromo?.trim()) {
        // Calcular porcentaje de descuento para mostrar
        porcentajeDescuento = ((1 - descuentoAplicado) * 100).toFixed(0);
      
        if ($("#reserva_codepromo").length === 0) {
          $("#reserva_items").closest('.card-body').append(`
            <p style class="mt-3">
              <strong>Código de Promoción:</strong>
              <span id="reserva_codepromo" style="background: #e91e63; color: white; padding: 10px; border-radius: 5px; margin: 0 10px 0 0;">
                ${reserva.codepromo}
              </span>
              <small style="color:#000;">Descuento: ${getPorcentajeDescuento()}%</small>
            </p>
          `);
        } else {
          // Actualizamos el código promo y el descuento
          $("#reserva_codepromo").text(reserva.codepromo);
          $("#reserva_codepromo").siblings('small').text(`Descuento aplicado: ${porcentajeDescuento}%`);
        }
      }
      // Check-in
      const checkinVal = reserva.checkin;
      const checkinSwitch = $("#reserva_checkin_switch");
      const checkinLabel = $("label[for='reserva_checkin_switch']");
    
      if (checkinVal === null || checkinVal === undefined) {
        checkinSwitch.prop("checked", false).prop("disabled", true);
        checkinLabel.removeClass("text-success text-danger").addClass("text-muted");
      } else if (checkinVal == 0) {
        checkinSwitch.prop("checked", false).prop("disabled", false);
        checkinLabel.removeClass("text-success text-muted").addClass("text-danger"); // rojo
      } else if (checkinVal == 1) {
        checkinSwitch.prop("checked", true).prop("disabled", false);
        checkinLabel.removeClass("text-danger text-muted").addClass("text-success"); // verde
      }
    
      // No-Show
      const noshowVal = reserva.noshow;
      const noshowCheckbox = $("#reserva_noshow");
      const noshowLabel = noshowCheckbox.closest("p").find("strong");

      if (noshowVal == 1) {
        noshowCheckbox.prop("checked", true).prop("disabled", false);
        noshowLabel.removeClass("text-success text-muted").addClass("text-danger"); // rojo
      } else {
        // Si es 0, null o undefined, se comporta igual
        noshowCheckbox.prop("checked", false).prop("disabled", false);
        noshowLabel.removeClass("text-danger text-muted").addClass("text-success"); // verde
      }

      
    }
    function formatearFecha(fechaOriginal) {
      // Crear objeto Date desde la cadena
      const fecha = formatFechas(fechaOriginal);
      return fecha.f5;
  }
  
    

    function calcularTotal() {
      let total = 0;

      $('#reserva_items tr').each(function () {
        const cantidadTexto = $(this).find('td:nth-child(1)').text().trim();
        const precioTexto = $(this).find('td:nth-child(3)').text();
    
        const cantidad = parseInt(cantidadTexto) || 0;
        const precioUnitario = parseFloat(precioTexto.replace(/[^0-9.]+/g, "")) || 0;
    
        total += cantidad * precioUnitario;
      });
    
      // Fallback si total es 0
      if (total === 0) {
        total = parseFloat(reserva.total || 0);
      }
      
      const totalConDescuento = descuentoAplicado > 0 ? total * descuentoAplicado : total;
      const totalBase = total;
      const balance = parseFloat(reserva.balance ?? 0);
    
      let htmlTotal = "";
      let buttonEditPax = `
        <div class="d-flex align-items-center justify-content-end flex-wrap mb-2">
            <button class="btn btn-warning align-item-end p-2 text-white background-blue-2 border-0" id="btnEditarPax">Editar Pax</button>
        </div>
      `;
      // 🔴 Lógica si id_estatus == 1
      if (parseInt(reserva.id_estatus) === 1) {
        htmlTotal += `
          <div>
            <span class="fs-4" style="font-weight: bold; color:#000;">
              Total:
              <strong>$${totalConDescuento.toFixed(2)} ${reserva.moneda}</strong>
            </span>
          </div>
          ${buttonEditPax}
          <div style="margin-top: 8px;">
            <span class="fs-22-px" style="background:#03a9f4; padding: 6px 12px; color:white; border-radius:4px; display:inline-block;">
              SIN BALANCE
            </span>
          </div>
        `;
    
        // if (descuentoAplicado > 0) {
        //   htmlTotal += `
        //     <div style="margin-top: 8px;">
        //       <span style="font-weight: bold; color:#000;">
        //         Descuento aplicado: $${((getPorcentajeDescuento() / 100) * reserva.total) + " "+ reserva.moneda}
        //       </span>
        //     </div>
        //   `;
        // }
    
        $('#reserva_total').html(htmlTotal);
        $('#pago_monto').text('0.00');
        return;
      }
    
      // 🟢 Lógica normal (otros estatus)
      if (descuentoAplicado > 0) {
        htmlTotal += `
          <div style="margin-bottom: 4px;">
            <span class="fs-4" style="font-weight: bold; color:#000;">
              Total:
              <strong style="text-decoration: line-through; color: #999;">
                $${totalBase.toFixed(2)} ${reserva.moneda}
              </strong>
            </span>
          </div>
          ${buttonEditPax}
          <div>
            <span style="font-weight: bold; color:#000;">
              Total con descuento:
              <strong style="color: #000;">
                $${totalConDescuento.toFixed(2)} ${reserva.moneda}
              </strong>
            </span>
          </div>
        `;
      } else {
        htmlTotal += `
          <div>
            <span class="fs-4" style="font-weight: bold; color:#000;">
              Total:
              <strong>$${totalBase.toFixed(2)} ${reserva.moneda}</strong>
            </span>
          </div>
          ${buttonEditPax}
        `;
      }
    
      // Estado del balance
      if (balance === 0) {
        htmlTotal += ` 
          <div style="margin-top: 8px;">
            <span class="fs-22-px" style="background:#03a9f4; padding: 6px 12px; color:white; border-radius:4px; display:inline-block;">
              SIN BALANCE
            </span>
          </div>
        `;
      } else {
        htmlTotal += `
          <div style="margin-top: 8px;">
            <span class="fw-bold" style="background:#03a9f4; padding: 6px 12px; color:white; border-radius:4px; display:inline-block;">
              BALANCE DE: <strong class="fs-22-px fw-normal">$${balance.toFixed(2)} ${reserva.moneda}</strong>
            </span>
          </div>
        `;
      }
    
      $('#reserva_total').html(htmlTotal);
      $('#pago_monto').text(balance.toFixed(2));

    }
    
    // Referencias a inputs usuario
    const camposUsuario = {
      nombre: $("#usuario_nombre"),
      apellido: $("#usuario_apellido"),
      email: $("#usuario_email"),
      telefono: $("#usuario_telefono"),
      hotel: $("#usuario_hotel"),
      habitacion: $("#usuario_cuarto")
    };

    // Valores originales para comparación
    let usuarioOriginal = {
      nombre: reserva.cliente_name ?? '',
      apellido: reserva.cliente_lastname ?? '',
      email: reserva.email ?? '',
      telefono: reserva.telefono ?? '',
      hotel: reserva.hotel ?? '',
      habitacion: reserva.habitacion ?? ''
    };

    // Inicializar inputs con valores de reserva y desbloquearlos
    Object.keys(camposUsuario).forEach(key => {
      camposUsuario[key].val(usuarioOriginal[key]).prop("readonly", false);
    });


    const debounceTimers = {};

    function debounceGuardar(campo, valor) {
      if (debounceTimers[campo]) clearTimeout(debounceTimers[campo]);
      debounceTimers[campo] = setTimeout(() => guardarCampo(campo, valor), 2500);
    }

    // Guardar campo individualmente
    async function guardarCampo(campo, valor) {
      const fieldId = camposUsuario[campo].attr("id"); // ej: "usuario_nombre" o "usuario_telefono"
    
      // 🔥 Validar dinámicamente según el ID
      const validation = validateFieldById(fieldId, valor);
      if (!validation.valid) {
        mostrarToastOnObject(validation.message, camposUsuario[campo], "danger");
        return; // no manda nada si no pasa validación
      }
    
      const data = { idpago: modalData.id, tipo: 'client', module: 'DetalleReservas' };
      if (campo === 'nombre') data.cliente_name = valor;
      else if (campo === 'apellido') data.cliente_lastname = valor;
      else data[campo] = valor;
      modalData[campo] = valor;
      try {
        const res = await fetchAPI("control", "PUT", { client: data });
        if (res.ok) {
          usuarioOriginal[campo] = valor;
          mostrarToastOnObject(`Campo ${campo} actualizado correctamente.`, camposUsuario[campo], "success");
        } else {
          mostrarToast(`Error al actualizar ${campo}.`, "danger");
        }
      } catch (err) {
        console.error(err);
        mostrarToastOnObject(`Error de conexión al actualizar ${campo}.`, camposUsuario[campo], "danger");
      }
    }

    // Escuchar cambios y aplicar debounce
    Object.keys(camposUsuario).forEach(campo => {
      camposUsuario[campo].on("input", function() {
        const valor = $(this).val();
        if (valor !== usuarioOriginal[campo]) debounceGuardar(campo, valor);
      });
    });


    renderReserva();
    // después de cargar la reserva:
    controlarBotonEditarPax(reserva);
    openReagendar()
   

    calcularTotal();

  } catch (error) {
    console.error("Error al obtener datos de la reserva:", error);
    alert("Hubo un error al cargar los datos.");
  }
  $("#btnEditarPax").on("click", openEditarPaxModal);

  $('#reserva_noshow').change(function() {
    const valor = $(this).is(':checked') ? 1 : 0;
    updateReservaCampo('noshow', 'noshow', valor);
  });
  
  $('#reserva_checkin_switch').change(function() {
    const valor = $(this).is(':checked') ? 1 : 0;
    updateReservaCampo('checkin', 'checkin', valor);
  });
  $('#enviar_checkin').off('click').on('click', function() {
      createCheckInNote(modalData);
  });


  async function createCheckInNote(modalData) {
    const mensaje = $('#comentario_checkin').val().trim();
    if (!mensaje) {
        alert("Escribe un mensaje antes de enviar.");
        return;
    }
        const idPago = modalData.id;

        const data = {
            create: {
                idpago: idPago,
                tipomessage: 'checkin',
                mensaje: mensaje,
                module: 'DetalleReservas'
            }
        };

        console.log("Enviando nota CHECKIN:", data);
          try {
              const response = await fetchAPI("message", "POST", data);
              const json = await response.json();
              console.log("Respuesta mensaje:", json);

              if (!response.ok) {
                  showErrorModal("Error al guardar la nota para SAPA ID " + idSapa);
              }else{
                showSuccessModal(json.message || 'Nota creada correctamente.')

              }
              

          } catch (error) {
              console.error("Error al enviar nota para SAPA ID:", idSapa, error);
              showErrorModal("Error al enviar nota");
          }

    console.log("Notas generadas correctamente para todas las SAPAs activas.");
}

async function updateReservaCampo(action, campo, valor) {
    const data = {
        [action]: {
        idpago: modalData.id,
        module: "DetalleReservas",
        tipo: action,
        [campo]: valor,
        }
    };
    try {
        const response = await fetchAPI("control", "PUT", data);
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "Error al actualizar");
        mostrarToast(`Campo ${campo} actualizado con éxito.`);
        location.reload();
        return true;

    } catch (err) {
        console.error(`❌ Error al actualizar ${campo}:`, err);
        mostrarToast(`Error al actualizar ${campo}`, "danger");
        return false;
    }
}
async function openReagendar() {
  const btnReagendar = document.getElementById("btnReagendarReserva");
  const dateInput = document.getElementById("datepickerReagendar");
  const spanFecha = document.getElementById("reserva_fecha");
  const fp = flatpickr(dateInput, {
      dateFormat: "Y-m-d",
      defaultDate: fechaOriginal.f9,
      static: true,
      appendTo: document.getElementById("flatpickrAnchor"),
      position: "below",

      onChange: function(selectedDates, dateStr) {
          if (!dateStr) return;

          spanFecha.textContent = fechaOriginal.f10;

          console.log("modalData que llega a openReagendar:", modalData);

          // Aquí truena si modalData está vacío
          openReagendarModal(modalData, dateStr);
      }
  });

  btnReagendar.addEventListener("click", () => fp.open());
}
$('#modalGeneric').on('hide.bs.modal', function () {
  const $modal = $('#modalGeneric');
  // Si el foco está dentro del modal, quitarlo
  if ($modal.has(document.activeElement).length) {
      $(document.activeElement).blur(); // 🔥 Esto previene el warning
  }
});

});