const bodyCache = {};

async function renderNotifications(search = '') {
    const data = await search_notificatios(search);
    const $tbody = $('#MRBuscador');
    $tbody.empty();

    if (!data || !data.length) {
        $tbody.html(`<tr><td colspan="5" class="text-center text-muted py-3">No hay registros</td></tr>`);
        return;
    }

    data.forEach((grupo) => {

        // -------------------------
        // Tarjeta del Grupo (header)
        // -------------------------
        const trGrupo = $(`
            <tr class="grupo-nog" data-nog="${grupo.nog}">
                <td colspan="5" class="p-0">

                    <div class="border rounded mb-2 nog-card">

                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-top grupo-header" style="cursor:pointer;">
                            <div>
                                <strong class="fs-6">NOG ${grupo.nog}</strong>
                                <div class="small text-muted">
                                    Total: ${grupo.total} • Activos: ${grupo.activos} • Vistos: ${grupo.vistos}
                                </div>
                            </div>

                            <span class="material-icons toggle-icon">
                                expand_more
                            </span>
                        </div>

                    </div>

                </td>
            </tr>
        `);

        $tbody.append(trGrupo);

        // -------------------------
        // Click expand / collapse
        // -------------------------
        trGrupo.find(".grupo-header").on("click", function () {
            const $row = trGrupo;
            const nog = grupo.nog;

            const $icon = $(this).find(".toggle-icon");

            // Si ya está abierto → cerrar
            if ($row.next().hasClass("nog-details")) {
                $row.next().remove();
                $icon.text("expand_more");
                return;
            }

            // Cerrar otros
            $(".nog-details").remove();
            $(".toggle-icon").text("expand_more");

            // -------------------------
            // Contenedor de mensajes
            // -------------------------
            const detalles = $(`
                <tr class="nog-details">
                    <td colspan="5" class="p-0">
                        <div class="p-3 bg-white border-start border-end border-bottom rounded-bottom">

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

                                        const fecha = msg.send_date
                                            ? new Date(msg.send_date).toLocaleString()
                                            : '-';

                                        const status = msg.status == "1" 
                                            ? `<span class="text-success"><span class="material-icons md-18">check_circle</span></span>`
                                            : `<span class="text-danger"><span class="material-icons md-18">cancel</span></span>`;

                                        const visto = msg.vistoC == "1"
                                            ? `<span class="text-info"><span class="material-icons md-18">visibility</span></span>`
                                            : `<span class="text-secondary"><span class="material-icons md-18">visibility_off</span></span>`;

                                        const btnId = `mail-${Date.now()}-${idx}`;

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
                                                        Ver Correo
                                                    </button>
                                                </td>
                                            </tr>
                                        `;
                                    }).join("")}

                                </tbody>

                            </table>

                        </div>
                    </td>
                </tr>
            `);

            $row.after(detalles);
            $icon.text("expand_less");

            // -------------------------
            // Evento ver correo
            // -------------------------
            detalles.find(".ver-correo-btn").on("click", async function () {
                const id = $(this).data("id");
                const config = bodyCache[id];

                const mails = await search_mails_by_nog_action(config.nog, config.accion);

                if (!mails || !mails.length) {
                    mostrarToastMail("No se encontraron correos", "danger");
                    return;
                }

                const filtrados = mails.filter(mail => {
                    try {
                        const newData = JSON.parse(mail.new_data || '{}');
                        if (newData.idMail === config.idMail) return true;

                        const oldData = JSON.parse(mail.old_data || '[]');
                        return Array.isArray(oldData) && oldData.some(od => od.idMail === config.idMail);

                    } catch { return false; }
                });

                if (!filtrados.length) {
                    mostrarToastMail("Correo no encontrado", "danger");
                    return;
                }

                let newData = {};
                try { newData = JSON.parse(filtrados[0].new_data || '{}'); } catch {}

                $("#modalCorreoBody").html(newData.body || "<em>Correo vacío</em>");
                new bootstrap.Modal("#modalCorreo").show();
            });

        });

    });
}
