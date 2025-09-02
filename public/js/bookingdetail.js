document.addEventListener("DOMContentLoaded", async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const nog = urlParams.get("nog");
    console.log(urlParams);
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
  
      const items = JSON.parse(reserva.items_details || "[]");
  
      // Función para renderizar datos de reserva e items
      function renderReserva() {
        // Datos usuario
        $("#usuario_nombre").text(`${reserva.cliente_name ?? 'N/A'} ${reserva.cliente_lastname ?? ''}`.trim());
        $("#usuario_email").text(reserva.email ?? 'N/A');
        $("#usuario_telefono").text(reserva.telefono ?? 'N/A');
        $("#usuario_hotel").text(reserva.hotel ?? 'N/A');
        $("#usuario_cuarto").text(reserva.habitacion ?? 'N/A');
  
        // Datos reserva
        $("#reserva_actividad").text(reserva.actividad ?? 'N/A');
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
        $('#reserva_items tr').each(function () {
          const precio = parseFloat($(this).find('td:last').text().replace('$','')) || 0;
          total += precio;
        });
  
        const totalConDescuento = descuentoAplicado > 0 ? total * descuentoAplicado : total;
        const porcentajeDescuento = ((1 - descuentoAplicado) * 100).toFixed(0);
  
        if (descuentoAplicado > 0) {
          $('#reserva_total').html(`
            <span style="text-decoration:line-through; color:#999;">$${total.toFixed(2)} USD</span><br>
            <span style="color:green; font-weight:bold;">$${totalConDescuento.toFixed(2)} USD</span><br>
            <small style="color:orange;">Descuento aplicado: ${porcentajeDescuento}%</small>
          `);
          $('#pago_monto').text(totalConDescuento.toFixed(2));
        } else {
          $('#reserva_total, #pago_monto').text(total.toFixed(2));
        }
      }
  
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
      cargarDatosUsuario(modalData);

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
    
  });
// ========================
// SECCIÓN: DATOS USUARIO EDITABLES
// ========================
let usuarioOriginal = {};

function cargarDatosUsuario(reserva){
  usuarioOriginal = {
    email: reserva.email ?? 'N/A',
    telefono: reserva.telefono ?? 'N/A',
    hotel: reserva.hotel ?? 'N/A',
    cuarto: reserva.habitacion ?? 'N/A'
  };

  $("#usuario_nombre").val(`${reserva.cliente_name ?? 'N/A'} ${reserva.cliente_lastname ?? ''}`).prop("readonly", true);
  $("#usuario_email").val(usuarioOriginal.email).prop("readonly", true);
  $("#usuario_telefono").val(usuarioOriginal.telefono).prop("readonly", true);
  $("#usuario_hotel").val(usuarioOriginal.hotel).prop("readonly", true);
  $("#usuario_cuarto").val(usuarioOriginal.cuarto).prop("readonly", true);
}

// Detectar cambios en campos editables
$("#usuario_email, #usuario_telefono, #usuario_hotel, #usuario_cuarto").on("input", function(){
  const hayCambio = 
    $("#usuario_email").val() !== usuarioOriginal.email ||
    $("#usuario_telefono").val() !== usuarioOriginal.telefono ||
    $("#usuario_hotel").val() !== usuarioOriginal.hotel ||
    $("#usuario_cuarto").val() !== usuarioOriginal.cuarto;

  $("#btn_guardar_usuario").toggleClass("d-none", !hayCambio);
  $("#btn_cancelar_edicion").toggleClass("d-none", !hayCambio);
});

$("#btn_editar_usuario").on("click", function(){
  $("#usuario_email, #usuario_telefono, #usuario_hotel, #usuario_cuarto")
    .prop("readonly", false)       // <-- esto desbloquea
    .removeClass("bg-light").addClass("bg-white");
  $("#btn_cancelar_edicion, #btn_guardar_usuario").removeClass("d-none");
});


// Botón cancelar
$("#btn_cancelar_edicion").on("click", function(){
  $("#usuario_email").val(usuarioOriginal.email).prop("readonly", true);
  $("#usuario_telefono").val(usuarioOriginal.telefono).prop("readonly", true);
  $("#usuario_hotel").val(usuarioOriginal.hotel).prop("readonly", true);
  $("#usuario_cuarto").val(usuarioOriginal.cuarto).prop("readonly", true);

  $("#btn_guardar_usuario, #btn_cancelar_edicion").addClass("d-none");
});

// Botón guardar
$("#btn_guardar_usuario").on("click", async function(){
  const data = {
    idpago: modalData.id,
    email: $("#usuario_email").val(),
    telefono: $("#usuario_telefono").val(),
    hotel: $("#usuario_hotel").val(),
    habitacion: $("#usuario_cuarto").val(),
    tipo: 'client',
    module: 'DetalleReservas'
  };

  try {
    const response = await fetchAPI("control", "PUT", { client: data });
    if(response.ok){
      usuarioOriginal = {...data};
      $("#usuario_email, #usuario_telefono, #usuario_hotel, #usuario_cuarto").prop("readonly", true);
      $("#btn_guardar_usuario, #btn_cancelar_edicion").addClass("d-none");
      alert("Datos actualizados correctamente.");
    } else alert("Error al guardar los datos.");
  } catch(error){
    console.error(error);
    alert("Error en la conexión al guardar.");
  }
});