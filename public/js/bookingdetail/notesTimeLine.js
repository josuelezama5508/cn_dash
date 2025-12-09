let mensajeEditando = null;
$('#btnAgregarNota').on('click', function () {
  openMessagesNotesModal(null, modalData);
});

// Abrir modal mensajes
function abrirModalCorreos() {
  $('#modalCorreos').addClass('show');
  cargarCorreos(modalData.nog); // cargar mensajes para idPago actual
  // $('#mensajeEditTexto').val('');
  // $('#btnGuardarMensaje').prop('disabled', true);
}

// Cerrar modal mensajes
function cerrarModalCorreos() {
  $('#modalCorreos').removeClass('show');
}

// Cargar mensajes y mostrar en modal usando search_messages
async function cargarCorreos(nog) {
  try {
    const mensajes = await search_notificatios_mail(nog);
    const $container = $('#mensajesContainer');
    $container.empty();

    if (!mensajes || mensajes.length === 0) {
      $container.html('<p class="text-muted text-center">No hay mensajes.</p>');
      return;
    }

    mensajes.forEach((mensaje) => {
      const fecha = mensaje.send_date
        ? new Date(mensaje.send_date).toLocaleString()
        : '';

      const visto = mensaje.vistoC == 1;

      const cartaHTML = `
        <div class="card mb-2 border-0 carta-correo ${visto ? 'bg-light' : 'bg-white'}">
          <div class="card-body d-flex justify-content-between align-items-center px-3 py-2">
            <div class="d-flex align-items-center">
              <i class="fas ${visto ? 'fa-envelope-open text-success' : 'fa-envelope text-secondary'} fs-5 me-3"></i>
              <span class="fw-semibold">${mensaje.accion || 'Sin acción'}</span>
            </div>
            <div class="text-end">
              <span class="text-muted small">${fecha}</span>
            </div>
          </div>
        </div>
      `;

      $container.append(cartaHTML);
    });

  } catch (error) {
    console.error(error);
    $('#mensajesContainer').html('<p class="text-danger text-center">Error cargando mensajes.</p>');
  }
}


// Guardar mensaje editado
$('#btnGuardarMensaje').on('click', async () => {
    if (!mensajeEditando) return showErrorModal('Selecciona un mensaje para editar.');
    
    const nuevoTexto = $('#mensajeEditTexto').val().trim();
    if (!nuevoTexto) return showErrorModal('El mensaje no puede estar vacío.');
  
    try {
        const data = {
            id: mensajeEditando.id,
            mensaje: nuevoTexto,
            module: "DetalleReservas"
        };

        // Llamada PUT para actualizar mensaje
        const response = await update_message({ update: data });
        console.log("Respuesta de update_message:", response);
        
        if (!response) showErrorModal('Error al actualizar mensaje.');
        

        //   alert('Mensaje actualizado.');
        
        // recargar mensajes usando el idPago (modalData.idPago o como tengas almacenado)
        cargarCorreos(modalData.nog);

        // $('#mensajeEditTexto').val('');
        // $('#btnGuardarMensaje').prop('disabled', true);
        mensajeEditando = null;

    } catch (error) {
      showErrorModal('No se pudo actualizar el mensaje. Intenta de nuevo.');
        console.error(error);
    }
  });

function activarBotonesEditar() {
  document.querySelectorAll(".btnEditarMensaje").forEach(btn => {
      btn.addEventListener("click", function() {
          const id = this.dataset.id;

          const texto = document.getElementById(`mensaje-${id}`).innerText.trim();
          const tipo = document.getElementById(`tipo-${id}`).innerText.trim().toLowerCase();
          openMessagesNotesModal({id, texto, tipo}, modalData)
      });
  });
}
async function renderUltimoMensajeContent(idpago) {
  const mensajes = await search_messages(idpago);

  const contenedor = document.getElementById("ultimoMensajeReserva");
  const btnBox = document.getElementById("btnEditarBox");
  const iconosBox = document.getElementById("mensajeTipoBox");
  const mensajeBox = document.getElementById("mensajeDetalleBox");

  contenedor.style.display = "flex";

  btnBox.innerHTML = "";
  iconosBox.innerHTML = "";
  mensajeBox.innerHTML = "";

  // Contenedor scrollable para iconos+datos
  const iconosContainer = document.createElement("div");
  iconosContainer.className = "d-flex flex-column gap-2 overflow-x-hidden p-2 rounded-1";
  iconosContainer.style.maxHeight = "130px";
  iconosContainer.style.overflowY = "auto";
// =========================================
//         CASO SIN MENSAJES
// =========================================
if (!mensajes || mensajes.length === 0) {

  iconosContainer.innerHTML = `
    <div class="px-1 py-4 border rounded-1 text-muted text-center small border-gray-custom-2">
      Sin mensajes
    </div>
  `;
  iconosBox.appendChild(iconosContainer);

  mensajeBox.innerHTML = `
    <div class="border rounded-1 px-1 py-4 bg-white w-100 text-muted text-center" style="border-color:#e05050 !important;">
      Sin nota
    </div>
  `;

  // Misma estructura y estilos que tu botón de editar
  btnBox.innerHTML = `
    <button 
        id="btn-edit-empty"
        class="btn btn-sm btn-warning px-4 py-2 text-white background-blue-dark-1 border-0 d-flex justify-content-center align-items-center w-75 position-relative"
        onclick="openMessagesNotesModal(null, modalData)"
    >
        <i class="material-icons d-flex justify-content-center align-items-center" style="font-size:20px;">add</i>
    </button>

    <span class="position-absolute w-75 fw-normal bordered-1 text-white background-orange-custom-2 text-center"
        style="
            font-size:12px;
            top:80%;
            left:45%;
            transform:translate(-50%, -50%);
        ">
      0 Mensajes
    </span>
  `;

  return;
}

  // =========================================
  //        CASO CON MENSAJES
  // =========================================
  mensajes.forEach((msg, index) => {
    const letra = (msg.name || "Usuario").charAt(0).toUpperCase();
    const tipo = msg.tipomessage || "nota";
    const usuario = msg.name || "Usuario";
    const userLower = (msg.username || "sistema").toLowerCase();
    const fecha = formatFechas(msg.datestamp);
    const flotante = `<span class="position-absolute w-auto px-2 fw-normal bordered-1 text-white background-blue-5 text-center" style="font-size:12px; top:0px; right:20px; transform:translate(0, 50%); border-radius:4px; "> ULTIMO</span>`;
    const fila = document.createElement("div");
    fila.className = "row align-items-center g-2 p-1 bg-white rounded cursor-pointer hoverable-row border-gray-custom-2 mb-1 position-relative";
    fila.innerHTML = `
      <div class="col-auto d-flex justify-content-center align-items-center">
        <div class="rounded-circle d-flex justify-content-center align-items-center"
             style="width:45px;height:45px;background-color:#E91E63;color:white;font-weight:bold;">
             ${letra}
        </div>
      </div>
      <div class="col small position-relative">
        <div class="fs-16-px">
          <i class="material-icons ${(tipo === "nota") ? "text-blue-custom" : ((tipo === "importante") ? "text-red-custom" : "text-yellow-custom") }">comment</i>
          <strong>Tipo:</strong> <span id="tipo-${msg.id}" class="${(tipo === "nota") ? "text-blue-custom" : ((tipo === "importante") ? "text-red-custom" : "text-yellow-custom") }">${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</span>
        </div>
        <div class="fs-16-px"><strong>Modificado:</strong> ${usuario}</div>
        <div class="fs-16-px"><strong>Usuario:</strong> ${userLower}</div>
        <div class="fst-italic text-muted text-end mt-1 fs-12">${fecha.f1}</div>
        ${index === 0 ? flotante : '' }
      </div>
    `;

    fila.addEventListener("click", () => {
      iconosContainer.querySelectorAll(".selected-row").forEach(el => el.classList.remove("selected-row"));
      fila.classList.add("selected-row");

      mensajeBox.innerHTML = `
        <div class="row align-items-center g-3 ms-0 me-0 p-3 mb-3 bg-white w-100">
          <div class="col-12 col-sm-12 col-lg-12 p-3 border rounded-1" style="border-color:#e05050 !important; word-break:break-word;">
            <div class="fw-bold mb-2">Nota:</div>
            <div id="mensaje-${msg.id}" class="fst-italic fs-4 text-gray-dark-custom">${msg.mensaje || "(Mensaje vacío)"}</div>
          </div>
        </div>
      `;

      btnBox.innerHTML = `
        <button id="btn-edit-${msg.id}" 
            class="btn btn-sm btn-warning btnEditarMensaje px-4 py-2 text-white background-blue-dark-1 border-0 d-flex justify-content-center align-items-center w-75 position-relative"
            data-id="${msg.id}">
            <i class="material-icons d-flex justify-content-center align-items-center" style="font-size:20px;">edit</i>
        </button>

        <span class="position-absolute w-75 fw-normal bordered-1 text-white background-orange-custom-2 text-center"
              style="
                  font-size:12px;
                  top:65%;
                  left:45%;
                  transform:translate(-50%, -50%);
              ">
          ${mensajes.length} Mensajes
        </span>
      `;

      activarBotonesEditar();
    });

    iconosContainer.appendChild(fila);

    // Selección inicial
    if (index === 0) fila.click();
  });

  iconosBox.appendChild(iconosContainer);
}


function editarNota(id, mensaje, tipo) {
  console.log("Editando ID:", id);
  
  // Guardas el mensaje actual para usar en tu PUT
  mensajeEditando = { id, mensaje, tipo };

  // Rellenas tu form
  $('#nuevaNota').val(mensaje);
  $('#typenote').val(tipo);

  // Muestras el formulario
  $('#formularioNota').slideDown();
  $('#btnAgregarNota').html('<i class="fas fa-times-circle"></i> Cancelar nota');
}
// Utilidad para formatear fecha (opcional)
function formatDateTime(datetimeString) {
    const fecha = new Date(datetimeString);
    return fecha.toLocaleString("es-ES", {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

