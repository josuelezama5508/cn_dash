let mensajeEditando = null;
$('#btnAgregarNota').on('click', function () {
  const $formulario = $('#formularioNota');
  const isVisible = $formulario.is(':visible');

  if (isVisible) {
      $formulario.slideUp();
      $('#btnAgregarNota').html('<i class="fas fa-plus-circle"></i> Agregar nota');
  } else {
      $formulario.slideDown();
      $('#btnAgregarNota').html('<i class="fas fa-times-circle"></i> Cancelar nota');
  }
});

$('#btnEnviarNota').on('click', async function() {
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
        console.log(data);
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
function abrirModalCorreos() {
  $('#modalCorreos').addClass('show');
  cargarCorreos(modalData.nog); // cargar mensajes para idPago actual
  // $('#mensajeEditTexto').val('');
  // $('#btnGuardarMensaje').prop('disabled', true);
}

// Cerrar modal mensajes
function cerrarModalCorreos() {
  $('#modalCorreos').removeClass('show');
  mensajeEditando = null;
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
        <div class="card mb-2 border-0 shadow-sm carta-correo ${visto ? 'bg-light' : 'bg-white'}">
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
        cargarCorreos(modalData.nog);

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

  box.innerHTML = ''; // Limpiar contenido

  contenedor.style.display = "block";

  // Crear contenedor para las notas adicionales ocultas
  const extrasContainer = document.createElement('div');
  extrasContainer.style.display = "none";
  extrasContainer.id = "mensajesExtraContainer";

  mensajes.forEach((mensaje, index) => {
      const imagenPerfil = "https://cdn-icons-png.flaticon.com/512/149/149071.png";
      const { class: badgeClass, icono, texto: etiquetaTexto } = obtenerDatosEtiqueta(mensaje.tipomessage);
      const isPrincipal = index === 0;

      const mensajeHTML = `
          <div class="alert alert-secondary border-secondary mensaje-item ${isPrincipal ? 'principal' : 'respuesta'}">
              <div class="d-flex align-items-center mb-2">
                  <i class="fas fa-user-circle me-2 ${isPrincipal ? 'text-white' : 'text-dark'}" style="font-size: 2.5rem;"></i>

                  <div>
                      <strong class="${isPrincipal ? 'text-white' : 'text-dark'}">${mensaje.name || 'Usuario'} ${mensaje.lastname || ''}</strong><br>
                      <small class="${isPrincipal ? 'text-white' : 'text-dark'}">${formatDateTime(mensaje.datestamp)}</small>
                      <span class="${badgeClass}" style="padding: 6px;">${icono} ${etiquetaTexto}</span>
                  </div>
              </div>
              <p class="mb-0 ${isPrincipal ? 'text-white' : 'text-dark'}">${mensaje.mensaje || "(Mensaje vacío)"}</p>
          </div>
      `;

      // Agregar el primero directamente
      if (isPrincipal) {
          box.innerHTML += mensajeHTML;
      } else {
          extrasContainer.innerHTML += mensajeHTML;
      }
  });

  if (mensajes.length > 1) {
      const toggleBtn = document.createElement('button');
      toggleBtn.className = "btn btn-sm btn-outline-light mt-2";
      toggleBtn.style.backgroundColor = "#709ac2";
      toggleBtn.style.borderColor = "#7e9cc0";
      toggleBtn.style.marginBottom = "1rem";
      toggleBtn.innerHTML = '<i class="fas fa-eye me-2"></i> Mostrar más';

      toggleBtn.onclick = () => {
          const isHidden = extrasContainer.style.display === "none";
          extrasContainer.style.display = isHidden ? "block" : "none";
          toggleBtn.innerHTML = isHidden
            ? '</i><i class="fas fa-eye-slash me-2"></i> Mostrar menos'
            : '<i class="fas fa-eye me-2"></i> Mostrar más';
      };

      box.appendChild(toggleBtn);
      box.appendChild(extrasContainer);
  }
}

// Utilidad para formatear fecha (opcional)
function formatDateTime(datetimeString) {
    const fecha = new Date(datetimeString);
    return fecha.toLocaleString("es-ES", {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

