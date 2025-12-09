// modalEditSapa.js
// 1. Renderiza el contenido del modal
window.renderEditSapaModal = function (estadoSeleccionado = 0) {
    return `
        <div class="mb-3">
            <label class="form-label fw-bold">Estado SAPA</label>
            <div id="divStatusSapaSelect"></div>
        </div>
    `;
};

// 2. Implementa la lógica del modal
window.openEditSapaModal = async function (id) {
    // Renderizar el contenido en el modal genérico
    const html = renderEditSapaModal();
    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Estado de la SAPA";
    // 3. INYECTAR BOTONES EN EL FOOTER
    $("#modal_generic_footer").html(`
        <button id="btnCancelarGeneric" type="button" class="btn btn-danger py-1 px-3 rounded-1" data-bs-dismiss="modal">
            Cancelar
        </button>
        <button id="btnGuardarGeneric" type="button" class="btn btn-primary background-green-custom py-1 px-3 rounded-1">
            Actualizar
        </button>
    `);
    $('#modalGeneric').removeClass(function (_, className) {
        return (className.match(/w-\d+/g) || []).join(' ');
    });
    $('#modalGeneric').addClass('w-25');

    // Inicializar modal Bootstrap
    const modalEl = document.getElementById("modalGeneric");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    window.currentModal = modal;

    // Obtener datos de SAPA
    const sapaData = await search_id(id);
    const estadoSeleccionado = sapaData?.id_estatus_sapa ?? 0;
    console.log("ESTADO SELECCIONADO", estadoSeleccionado);

    // Renderizar el select dinámico
    $.ajax({
        url: `${window.url_web}/widgets/`,
        type: "POST",
        data: {
            widget: "select",
            category: "statussapa",
            name: "statussapa",
            selected_id: estadoSeleccionado,
            'id_user': window.userInfo.user_id
        },
        success: function (response) {
            $("#divStatusSapaSelect").html(response);
        },
        error: function () {
            $("#divStatusSapaSelect").html("<p>Error al cargar estados</p>");
        }
    });

    // Evento de guardar cambios
    $("#btnGuardarGeneric").on("click", async () => {
        const newStatus = $("select[name='statussapa']").val();

        try {
            const response = await fetchAPI("showsapa", "PUT", {
                id: id,
                status_sapa: newStatus,
                action: "Actualización de estado Sapa"
            });

            if (response.ok) {
                showErrorModal("Actualización exitosa");
                closeModal();
                location.reload();
            } else {
                showErrorModal("Error al guardar cambios");
            }
        } catch (e) {
            console.error(e);
        }
    });
    // 6. CANCELAR
    $("#btnCancelarGeneric").on("click", () => closeModal());
};

// 3. Cierre del modal (reutilizable)
window.closeModal = function () {
    if (window.currentModal) {
        window.currentModal.hide();
        window.currentModal = null;
    }

    // Limpiar contenido del modal
    $("#modalGenericContent").empty();
    $("#modalGenericTitle").text("");

    // Quitar cualquier evento previo de los botones del footer
    $("#modalGeneric .btn-primary").off("click");
    $("#modalGeneric .btn-secondary").off("click");
};
