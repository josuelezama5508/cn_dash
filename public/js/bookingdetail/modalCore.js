// modalCore.js
window.closeModal = function () {
    if (!currentModal) return;

    const modalEl = document.getElementById("modalGeneric");

    modalEl.addEventListener("hidden.bs.modal", function handler() {
        modalEl.removeEventListener("hidden.bs.modal", handler);

        $("#modalGenericContent").empty();
        $("#modal_generic_footer").empty();
        $("#modalGenericTitle").text("");

        modalEl.setAttribute("aria-hidden", "true");
        currentModal = null;
    });

    currentModal.hide();
};

// ======================
// MODAL CORE LIMPIO
// ======================
// Cerrar modal de forma segura
window.closeModal = function () {
    if (!currentModal) return;

    const modalEl = document.getElementById("modalGeneric");

    // Limpiar cuando Bootstrap ANIME el cierre
    modalEl.addEventListener("hidden.bs.modal", function handler() {
        modalEl.removeEventListener("hidden.bs.modal", handler);

        $("#modalGenericContent").empty();
        $("#modal_generic_footer").empty();
        $("#modalGenericTitle").text("");

        modalEl.setAttribute("aria-hidden", "true");
        currentModal = null;
    });

    currentModal.hide();
};

// Abrir modal de forma controlada
window.openModal = async function (url, modalData = {}, title = "", pregunta = "", txt_btn_save = "Guardar", txt_btn_cerrar = "Cancelar", posSave = 1, posCerrar = 2) {
    try {
        // ======================
        // RESET TOTAL ANTES DE CARGAR
        // ======================
        $("#modalGenericContent").empty();
        $("#modal_generic_footer").empty();
        $("#modalGenericTitle").text("");
        $("#modal_generic_footer").removeClass(function (_, className) {
            return (className.match(/justify-content-\S+/g) || []).join(" ");
        });
        $('#modalGeneric').removeClass(function (_, className) {
            return (className.match(/w-\d+/g) || []).join(' ');
        });
        $('#modalGeneric').addClass('w-50');
        // ======================
        // CARGAR HTML
        // ======================
        const res = await fetch(url);
        const html = await res.text();

        $("#modalGenericContent").html(html);
        $("#modalGenericTitle").text(title);

        // ======================
        // VALIDAR POSICIONES
        // ======================
        const validPositions = [1, 2];
        if (
            !validPositions.includes(posCerrar) ||
            !validPositions.includes(posSave) ||
            posCerrar === posSave
        ) {
            posCerrar = 1;
            posSave = 2;
        }

        // ======================
        // ARMAR FOOTER
        // ======================
        const btnCerrar = `
            <button id="btnCerrarGeneric"
                type="button"
                class="btn btn-danger py-1 px-2 rounded-1"
                style="width: 90px;"
                data-bs-dismiss="modal">
                ${txt_btn_cerrar}
            </button>`;

        const btnSave = `
            <button id="btnGuardarGeneric"
                type="button"
                class="btn background-green-custom btn-primary py-1 px-2 rounded-1"
                style="width: 90px;">
                ${txt_btn_save}
            </button>`;

        const footer = [];
        footer[posCerrar - 1] = btnCerrar;
        footer[posSave - 1] = btnSave;
        let htmlFooter = "";

        // Si NO hay pregunta → solo botones (tu caso de "cancelar")
        if (pregunta && pregunta.trim() !== "") {
            $("#modal_generic_footer").addClass("justify-content-start");
            // Si SÍ hay pregunta → título arriba, botones abajo
            htmlFooter = `
            <div class="col-12">
                <div class="w-100 text-start mb-2">
                    <span class="fw-semibold fs-6 text-body">${pregunta}</span>
                </div>
                    <div class="d-flex justify-content-start p-0 m-0 gap-2 w-100">
                    ${footer.join("")}
                </div>
            </div>
            
            `;
        } else {
            $("#modal_generic_footer").addClass("justify-content-end");

            htmlFooter = `
                <span id="pregunta_opcion"
                      class="me-3 fw-semibold text-gray fs-5">${pregunta}</span>
                ${footer.join("")}
            `;
        }
        
        $("#modal_generic_footer").html(htmlFooter);
        


        // ======================
        // INICIALIZAR MODAL
        // ======================
        const modalEl = document.getElementById("modalGeneric");
        modalEl.removeAttribute("aria-hidden");

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        currentModal = modal;

        // ======================
        // ASIGNAR ACCIÓN DEL BOTÓN
        // ======================
        const btnGuardar = document.getElementById("btnGuardarGeneric");

        if (url.includes("form_mail")) {
            btnGuardar.onclick = () => handleMail(modalData);
        } else if (url.includes("form_sapa")) {
            btnGuardar.onclick = () => confirmSapa(modalData);
        } else if (url.includes("form_cancelar")) {
            btnGuardar.onclick = () => handleMailCancel(modalData);
        } else if (url.includes("form_payment")) {
            btnGuardar.onclick = () => handlePayment(modalData);
        } else if (url.includes("form_update_sapa")) {
            btnGuardar.onclick = () => submitUpdateSapa(modalData);
        }else if (url.includes("form_send_voucher")) {
            btnGuardar.onclick = () => handleVoucher(modalData);
        } else {
            btnGuardar.onclick = () =>
                alert("Acción no implementada para este formulario.");
        }

    } catch (err) {
        console.error("Error al cargar el modal:", err);
        alert("Error al cargar el formulario.");
    }
};
