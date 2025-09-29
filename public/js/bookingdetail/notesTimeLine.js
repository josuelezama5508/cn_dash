let mensajeEditando = null;

$('#btnAgregarNota').on('click', async function() {
    const $textarea = $('#nuevaNota');
    const texto = $textarea.val().trim();
    const $inputtype = $('#typenote');
    const tipo = $inputtype.val();
    if (!texto) return alert("Escribe un comentario antes de enviar.");
    if (!tipo) return alert("Selecciona el tipo de nota antes de enviar.");

    try {
        const data ={mensaje: texto,
            idpago: modalData.id,
            tipomessage: tipo,
            module: "DetalleReservas"
        };
        const response = await fetchAPI('message', 'POST', {create: {...data}});

        if (!response.ok) throw new Error('Error al guardar la nota');

        // Opcional: podrías esperar la respuesta para tener datos más precisos
        // const nuevaNota = await response.json();

        // const notaItem = $(`
        //     <div class="list-group-item">
        //         <div class="d-flex w-100 justify-content-between">
        //             <h6 class="mb-1">Tipo: Nota</h6>
        //             <small>${new Date().toLocaleString()}</small>
        //         </div>
        //         <p class="mb-1"><em>${texto}</em></p>
        //         <small>Usuario: ${modalData.usuario || 'Desconocido'}</small>
        //     </div>
        // `);

        // $('#notasList').prepend(notaItem);
        if (modalData.id) {
          renderUltimoMensajeContent(modalData.id);
        }
        $textarea.val('');
    } catch (error) {
        alert('No se pudo guardar la nota. Intenta nuevamente.');
        console.error(error);
    }
});

// Abrir modal mensajes
function abrirModalMensajes() {
  $('#modalMensajes').addClass('show');
  cargarMensajes(modalData.id); // cargar mensajes para idPago actual
  // $('#mensajeEditTexto').val('');
  // $('#btnGuardarMensaje').prop('disabled', true);
}

// Cerrar modal mensajes
function cerrarModalMensajes() {
  $('#modalMensajes').removeClass('show');
  mensajeEditando = null;
}

// Cargar mensajes y mostrar en modal usando search_messages
async function cargarMensajes(idPago) {
    try {
      const mensajes = await search_messages(idPago);
  
      const $container = $('#mensajesContainer');
      $container.empty();
  
      if (!mensajes || mensajes.length === 0) {
        $container.html('<p>No hay mensajes.</p>');
        return;
      }
  
      mensajes.forEach(mensaje => {
        const $msgItem = $(`
          <div class="message-item p-2 border rounded mb-2">
            <div class="d-flex justify-content-between align-items-center">
              <small><strong>Tipo:</strong> ${mensaje.tipomessage || 'Nota'}</small>
              <small>${new Date(mensaje.datestamp).toLocaleString()}</small>
            </div>
            <p>${mensaje.mensaje}</p>
            <small><em>Usuario: ${mensaje.name || 'Desconocido'}</em></small>
          </div>
        `);
  
        // Al hacer click, cargar mensaje para editar
        // $msgItem.on('click', () => {
        //   mensajeEditando = mensaje;
        //   $('#mensajeEditTexto').val(mensaje.mensaje);
        //   $('#btnGuardarMensaje').prop('disabled', false);
        // });
  
        $container.append($msgItem);
      });
  
    } catch (error) {
      console.error(error);
      $('#mensajesContainer').html('<p class="text-danger">Error cargando mensajes.</p>');
    }
  }
  
// Guardar mensaje editado
$('#btnGuardarMensaje').on('click', async () => {
    if (!mensajeEditando) return alert('Selecciona un mensaje para editar.');
    
    const nuevoTexto = $('#mensajeEditTexto').val().trim();
    if (!nuevoTexto) return alert('El mensaje no puede estar vacío.');
  
    try {
        const data = {
            id: mensajeEditando.id,
            mensaje: nuevoTexto,
            module: "DetalleReservas"
        };

        // Llamada PUT para actualizar mensaje
        const response = await update_message({ update: data });
        console.log("Respuesta de update_message:", response);
        
        if (!response) throw new Error('Error al actualizar mensaje.');
        

        //   alert('Mensaje actualizado.');
        
        // recargar mensajes usando el idPago (modalData.idPago o como tengas almacenado)
        cargarMensajes(modalData.id);

        // $('#mensajeEditTexto').val('');
        // $('#btnGuardarMensaje').prop('disabled', true);
        mensajeEditando = null;

    } catch (error) {
        alert('No se pudo actualizar el mensaje. Intenta de nuevo.');
        console.error(error);
    }
  });
  
async function renderUltimoMensaje(idpago) {
  const mensajes = await search_last_messages(idpago);

  if (mensajes && mensajes.length > 0) {
      const mensaje = mensajes[0]; // Asumimos que es el último

      const contenedor = document.getElementById("ultimoMensajeReserva");
      const box = document.getElementById("mensajeTipoBox");
      const contenido = document.getElementById("contenidoUltimoMensaje");
      const info = document.getElementById("infoUltimoMensaje");

      // Insertar contenido del mensaje
      contenido.textContent = mensaje.mensaje || "(Mensaje vacío)";
      info.textContent = `— ${mensaje.name} | ${formatDateTime(mensaje.datestamp)}`;

      // Asignar clase según tipo de mensaje
      const tipo = mensaje.tipomessage;
      box.className = "alert"; // limpia clases previas

      switch (tipo) {
          case 'importante':
              box.classList.add("alert-danger");
              break;
          case 'balance':
              box.classList.add("alert-warning");
              break;
          default: // 'nota'
              box.classList.add("alert-info");
      }

      // Mostrar el contenedor
      contenedor.style.display = "block";
  }
}
function obtenerDatosEtiqueta(tipo) {
  switch (tipo) {
      case 'importante':
          return {
              class: "badge bg-warning text-dark",
              icono: '<i class="fas fa-exclamation-circle me-1"></i>',
              texto: "Importante"
          };
      case 'balance':
          return {
              class: "badge bg-success text-white",
              icono: '<i class="fas fa-dollar-sign me-1"></i>',
              texto: "Balance"
          };
      case 'nota':
      default:
          return {
              class: "badge bg-primary text-white",
              icono: '<i class="fas fa-sticky-note me-1"></i>',
              texto: "Nota"
          };
  }
}

async function renderUltimoMensajeContent(idpago) {
  const mensajes = await search_messages(idpago);

  const contenedor = document.getElementById("ultimoMensajeReserva");
  const box = document.getElementById("mensajeTipoBox");

  if (!mensajes || mensajes.length === 0) {
      contenedor.style.display = "none";
      return;
  }

  box.innerHTML = ''; // Limpiar

  contenedor.style.display = "block";

  mensajes.forEach((mensaje, index) => {
      const imagenPerfil = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

      const { class: badgeClass, icono, texto: etiquetaTexto } = obtenerDatosEtiqueta(mensaje.tipomessage);
      const isPrincipal = index === 0;

      const mensajeHTML = `
          <div class="alert alert-secondary border-secondary mensaje-item ${isPrincipal ? 'principal' : 'respuesta'}">
              <div class="d-flex align-items-center mb-2">
                  <img src="${imagenPerfil}" alt="Perfil" width="${isPrincipal ? '48' : '32'}" height="${isPrincipal ? '48' : '32'}" class="rounded-circle me-2 border">
                  <div>
                      <strong>${mensaje.name || 'Usuario'} ${mensaje.lastname || ''}</strong><br>
                      <small class="text-muted">${formatDateTime(mensaje.datestamp)}</small>
                      <span class="${badgeClass}" style="padding: 6px;">${icono} ${etiquetaTexto}</span>
                  </div>
              </div>

              <p class="mb-0">${mensaje.mensaje || "(Mensaje vacío)"}</p>
          </div>
      `;

      box.innerHTML += mensajeHTML;
  });
}

// Utilidad para formatear fecha (opcional)
function formatDateTime(datetimeString) {
    const fecha = new Date(datetimeString);
    return fecha.toLocaleString("es-ES", {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

