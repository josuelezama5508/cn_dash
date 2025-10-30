const bodyCache = {};
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
async function renderNotifications(search = '') {
  const data = await search_notificatios(search);
  const $tbody = $('#MRBuscador');
  $tbody.empty();

  if (!data || !data.length) {
    $tbody.html(`<tr><td colspan="5" class="text-center text-muted py-3">No hay registros</td></tr>`);
    return;
  }

  data.forEach((grupo, i) => {
    const trGrupo = $(`
      <tr class="grupo-nog" data-nog="${grupo.nog}">
        <td colspan="1"><strong>${grupo.nog}</strong> <i class="fas fa-chevron-down toggle-icon ms-2"></i></td>
        <td>${grupo.total || 0}</td>
        <td>${grupo.activos || 0}</td>
        <td>${grupo.vistos || 0}</td>
        <td></td>
      </tr>
    `);

    trGrupo.on('click', function () {
      const $row = $(this);
      const nog = $row.data('nog');

      // Si ya está abierto, cerramos
      if ($row.next().hasClass('mensajes-nog')) {
        $(`.mensajes-nog[data-nog="${nog}"]`).remove();
        $row.find('.toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        return;
      }

      // Cerrar otros grupos abiertos
      $('.mensajes-nog').remove();
      $('.toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');

      const mensajesHtml = `
        <tr class="mensajes-nog" data-nog="${grupo.nog}">
            <td colspan="5" class="p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                <tr>
                    <th>Acción</th>
                    <th>Fecha</th>
                    <th>Status</th>
                    <th>Visto</th>
                    <th>Correo</th>
                </tr>
                </thead>
                <tbody>
                ${grupo.mensajes.map((msg, idx) => {
                    const fecha = msg.send_date ? new Date(msg.send_date).toLocaleString() : '-';
                    const status = msg.status == "1" ? 'ACTIVO' : 'INACTIVO';
                    const visto = msg.vistoC == "1" ? 'Sí' : 'No';
                    const btnId = `btn-ver-correo-${Date.now()}-${idx}`;

                    // Guardamos los datos necesarios para luego abrir el correo
                    bodyCache[btnId] = {
                    nog: msg.nog,
                    accion: msg.accion,
                    idMail: msg.id
                    };

                    return `
                    <tr>
                        <td>${msg.accion}</td>
                        <td>${fecha}</td>
                        <td>${status}</td>
                        <td>${visto}</td>
                        <td>
                        <button class="btn btn-outline-primary btn-sm ver-correo-btn" data-id="${btnId}">
                            Ver correo
                        </button>
                        </td>
                    </tr>
                    `;
                }).join('')}
                </tbody>
            </table>
            </td>
        </tr>
        `;


      $row.after(mensajesHtml);
      $row.find('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');

      // Evento botón Ver correo
      $('.ver-correo-btn').off('click').on('click', async function () {
        const id = $(this).data('id');
        const config = bodyCache[id];
        const mails = await search_mails_by_nog_action(config.nog, config.accion);

        if (!mails || !mails.length) {
          mostrarToast("No se encontraron correos para este mensaje", "danger");
          return;
        }

        const filtrados = mails.filter(mail => {
          try {
            const newData = JSON.parse(mail.new_data || '{}');
            if (newData.idMail === config.idMail) return true;

            const oldData = JSON.parse(mail.old_data || '[]');
            return Array.isArray(oldData) && oldData.some(od => od.idMail === config.idMail);
          } catch (e) {
            console.warn("Error parseando JSON", e);
            return false;
          }
        });

        if (!filtrados.length) {
          mostrarToast("Correo no encontrado con el ID indicado", "danger");
          return;
        }

        let newData = {};
        try {
          newData = JSON.parse(filtrados[0].new_data || '{}');
        } catch (e) {
          console.warn("No se pudo parsear el correo body", e);
        }

        $('#modalCorreoBody').html(newData.body || '<em>Correo vacío</em>');
        const modal = new bootstrap.Modal(document.getElementById('modalCorreo'));
        modal.show();
      });
    });

    $tbody.append(trGrupo);
  });
}
