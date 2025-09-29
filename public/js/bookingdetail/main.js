document.addEventListener("DOMContentLoaded", async () => {
  const pathParts = window.location.pathname.split('/').filter(Boolean); // Quita vacíos
  const nog = pathParts[3]; // [0]=cn_dash, [1]=detalles-reserva, [2]=view, [3]=1MIMBR45MQ
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
    // 1. Registro del Service Worker (se hace al cargar la página)
  if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.register('/cn_dash/public/js/notificationservice/sw.js')
      .then(function(registration) {
        console.log('Service Worker registrado');
      })
      .catch(function(error) {
        console.error('Error al registrar Service Worker:', error);
      });
  } else {
    alert('Tu navegador no soporta Service Workers o Push API');
  }

  // 2. Evento click para pedir permiso y mostrar notificación
  $('#btn_prueba').on('click', function() {
    Notification.requestPermission().then(function(permission) {
      if (permission === 'granted') {
        // Payload para la notificación
        const payload = {
          title: 'Reserva creada',
          body: '¡Se ha creado una nueva reserva!',
          icon: '/icon.png',
          url: 'http://localhost/cn_dash/detalles-reserva/view/'
        };
  
        fetch('http://localhost/cn_dash/api/notificationservice?action=sendNotification', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            // Agrega aquí si usas token de autenticación, ejemplo:
            // 'Authorization': 'Bearer TU_TOKEN_AQUI'
          },
          body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
          console.log('Notificación enviada al servidor:', data);
          // alert('Notificación enviada al servidor. Revisa la consola.');
        })
        .catch(error => {
          console.error('Error al enviar notificación:', error);
          // alert('Error al enviar notificación. Revisa la consola.');
        });
  
      } else {
        // alert('Permiso de notificaciones denegado');
      }
    });
  });
  

    if (modalData.id) {
      renderUltimoMensajeContent(modalData.id);
      mostrarSapas(modalData.id);
    }
    // Botón cerrar modal
    $('#btnCerrarModalMensajes').on('click', cerrarModalMensajes);

    // Ejemplo: abrir modal cuando se presione un botón específico
    $('#btnAbrirMensajes').on('click', abrirModalMensajes);

    const items = JSON.parse(reserva.items_details || "[]");

    // Función para renderizar datos de reserva e items
    
    function renderReserva() {
      // Datos usuario
     camposUsuario.nombre.val(usuarioOriginal.nombre);
      camposUsuario.apellido.val(usuarioOriginal.apellido);
      camposUsuario.email.val(usuarioOriginal.email);
      camposUsuario.telefono.val(usuarioOriginal.telefono);
      camposUsuario.hotel.val(usuarioOriginal.hotel);
      camposUsuario.cuarto.val(usuarioOriginal.cuarto);

      // Datos reserva
      $("#reserva_actividad").text(reserva.actividad ?? 'N/A');
      $("#company_name").text(reserva.company_name ?? 'N/A');
      $("#reserva_fecha").text(reserva.datepicker ?? 'N/A');
      $("#reserva_hora").text(reserva.horario ?? 'N/A');
      $("#reserva_booking").text(reserva.nog ?? 'N/A');

      // Render items
      const itemsHtml = items.map(item => `
        <tr>
          <td>${item.item}</td>
          <td>${item.name}</td>
          <td>$${parseFloat(item.price).toFixed(2)}</td>
        </tr>
      `).join('');
      $("#reserva_items").html(itemsHtml);

      // Otros datos
      $("#pago_referencia").text(reserva.referencia ?? '');
      $("#reserva_balance").text(`$${parseFloat(reserva.total).toFixed(2)} USD`);
      $("#reserva_checkin").text("No").addClass("badge bg-danger");
      $("#reserva_canal").text(reserva.canal_nombre ?? "N/A");
      $("#reserva_rep").text(reserva.rep_nombre ?? "N/A");
      $("#reserva_tipo").text(reserva.type ?? "NORMAL");
      $('#pago_estado').text((reserva.proceso ?? "PENDIENTE").toUpperCase());
      $('#reserva_referencia').text((reserva.referencia ?? "N/A").toUpperCase());
      $("#reserva_fecha_compra").text(reserva.fecha_details ?? 'N/A');
      $("#reserva_metodo_pago").text("BALANCE");
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
        procesadoBadge.removeClass().addClass("badge bg-success text-white");
      } else {
        procesadoBadge.text("NO PROCESADO");
        procesadoBadge.removeClass().addClass("badge bg-danger text-white");
      }
      // check-in switch
      const checkinSwitch = $("#reserva_checkin_switch");
      const isCheckin = (reserva.checkin === true || reserva.checkin === 1 || reserva.checkin === "SI" || reserva.checkin === "si");

      checkinSwitch.prop("checked", isCheckin);

      const estadoClass = ["PAGADO", "PROCESADO"].includes(estado) ? "bg-success" : "bg-danger";
      $("#reserva_estado")
        .text(estado)
        .removeClass()
        .addClass(`badge ${estadoClass} text-white`);

      // Mostrar promo si aplica
      if (reserva.codepromo?.trim()) {
        if ($("#reserva_codepromo").length === 0) {
          $("#reserva_items").closest('.card-body').append(`
            <p><strong>Promoción aplicada:</strong> <span id="reserva_codepromo">${reserva.codepromo}</span></p>
          `);
        } else {
          $("#reserva_codepromo").text(reserva.codepromo);
        }
      }
    }

    let descuentoAplicado = 0;

    function calcularTotal() {
      let total = 0;
      console.log("Items contados:");
    
      $('#reserva_items tr').each(function () {
        const cantidadTexto = $(this).find('td:nth-child(1)').text().trim();
        const nombre = $(this).find('td:nth-child(2)').text().trim();
        const precioTexto = $(this).find('td:nth-child(3)').text();
        
        const cantidad = parseInt(cantidadTexto) || 0;
        const precioUnitario = parseFloat(precioTexto.replace(/[^0-9.]+/g, "")) || 0;
    
        const subtotalItem = cantidad * precioUnitario;
        console.log(`- ${nombre}: ${cantidad} x $${precioUnitario.toFixed(2)} = $${subtotalItem.toFixed(2)}`);
    
        total += subtotalItem;
      });
    
      if (total === 0) {
        const balance = parseFloat(reserva.balance || reserva.total || 0);
        total = balance;
        console.log(`Total calculado 0, usando balance: $${total.toFixed(2)}`);
      } else {
        console.log(`Total antes de descuento: $${total.toFixed(2)}`);
      }
    
      const totalConDescuento = descuentoAplicado > 0 ? total * descuentoAplicado : total;
      const porcentajeDescuento = ((1 - descuentoAplicado) * 100).toFixed(0);
    
      if (descuentoAplicado > 0) {
        $('#reserva_total').html(`
          <span style="text-decoration:line-through; color:#999;">$${total.toFixed(2)} USD</span><br>
          <span style="color:green; font-weight:bold;"><small style="color:green; font-weight:normal;">Total descuento: </small>$${totalConDescuento.toFixed(2)} USD</span><br>
          <small style="color:orange;">Descuento aplicado: ${porcentajeDescuento}%</small>
        `);
        $('#pago_monto').text(totalConDescuento.toFixed(2));
      } else {
        $('#reserva_total, #pago_monto').text(total.toFixed(2));
      }
    }
    
    // Referencias a inputs usuario
    const camposUsuario = {
      nombre: $("#usuario_nombre"),
      apellido: $("#usuario_apellido"),
      email: $("#usuario_email"),
      telefono: $("#usuario_telefono"),
      hotel: $("#usuario_hotel"),
      cuarto: $("#usuario_cuarto")
    };

    // Valores originales para comparación
    let usuarioOriginal = {
      nombre: reserva.cliente_name ?? '',
      apellido: reserva.cliente_lastname ?? '',
      email: reserva.email ?? '',
      telefono: reserva.telefono ?? '',
      hotel: reserva.hotel ?? '',
      cuarto: reserva.habitacion ?? ''
    };

    // Inicializar inputs con valores de reserva y desbloquearlos
    Object.keys(camposUsuario).forEach(key => {
      camposUsuario[key].val(usuarioOriginal[key]).prop("readonly", false);
    });

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
    // function mostrarToastOnObject(mensaje, $input, tipo = "success") {
    //   // Crear toast
    //   const toast = $(`
    //     <div class="toast align-items-center text-white ${tipo==='success'?'bg-success':'bg-danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position:absolute; z-index:9999;">
    //       <div class="d-flex">
    //         <div class="toast-body">${mensaje}</div>
    //         <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    //       </div>
    //     </div>
    //   `);
    
    //   // Agregar al body
    //   $("body").append(toast);
    
    //   // Calcular posición sobre el input
    //   const offset = $input.offset();
    //   toast.css({
    //     top: offset.top - toast.outerHeight() - 5, // arriba del input
    //     left: offset.left,
    //     minWidth: $input.outerWidth()
    //   });
    
    //   const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
    //   bsToast.show();
    
    //   // Remover al ocultarse
    //   toast.on('hidden.bs.toast', () => toast.remove());
    // }
    
    // Debounce para guardar cambios
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

    if (reserva.codepromo?.trim()) {
      const endpoint = `promocode?codecompany=${encodeURIComponent(reserva.code_company)}&codepromo=${encodeURIComponent(reserva.codepromo)}`;
      try {
        const promoResponse = await fetchAPI(endpoint, 'GET');
        const promoData = await promoResponse.json();

        if (promoResponse.ok && promoData.data?.length) {
          const descuento = parseFloat(promoData.data[0].descount) / 100;
          if (!isNaN(descuento)) {
            descuentoAplicado = 1 - descuento;
          } else {
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

    calcularTotal();

  } catch (error) {
    console.error("Error al obtener datos de la reserva:", error);
    alert("Hubo un error al cargar los datos.");
  }
  $("#reserva_checkin_switch").on("change", async function () {
    const nuevoEstado = $(this).prop("checked");
  
    try {
      const updateResponse = await fetchAPI("update_checkin", "POST", {
        nog: reserva.nog,
        checkin: nuevoEstado
      });
  
      if (!updateResponse.ok) {
        // revertir el switch en caso de error
        $(this).prop("checked", !nuevoEstado);
      }
    } catch (error) {
      // revertir el switch en caso de error de red
      $(this).prop("checked", !nuevoEstado);
    }
  });
  $("#btnEditarPax").on("click", openEditarPaxModal);

});