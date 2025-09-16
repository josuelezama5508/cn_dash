let mensajeEditando = null;

$('#btnAgregarNota').on('click', async function() {
    const $textarea = $('#nuevaNota');
    const texto = $textarea.val().trim();

    if (!texto) return alert("Escribe un comentario antes de enviar.");

    try {
        const data ={mensaje: texto,
            idpago: modalData.id,
            tipomessage: 'Nota',
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
  $('#mensajeEditTexto').val('');
  $('#btnGuardarMensaje').prop('disabled', true);
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
          <div class="message-item p-2 border rounded mb-2" style="cursor:pointer;">
            <div class="d-flex justify-content-between align-items-center">
              <small><strong>Tipo:</strong> ${mensaje.tipomessage || 'Nota'}</small>
              <small>${new Date(mensaje.datestamp).toLocaleString()}</small>
            </div>
            <p>${mensaje.mensaje}</p>
            <small><em>Usuario: ${mensaje.user_name || 'Desconocido'}</em></small>
          </div>
        `);
  
        // Al hacer click, cargar mensaje para editar
        $msgItem.on('click', () => {
          mensajeEditando = mensaje;
          $('#mensajeEditTexto').val(mensaje.mensaje);
          $('#btnGuardarMensaje').prop('disabled', false);
        });
  
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

        $('#mensajeEditTexto').val('');
        $('#btnGuardarMensaje').prop('disabled', true);
        mensajeEditando = null;

    } catch (error) {
        alert('No se pudo actualizar el mensaje. Intenta de nuevo.');
        console.error(error);
    }
  });
  

