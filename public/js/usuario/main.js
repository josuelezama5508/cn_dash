$(document).ready(function () {
    const $inputSearch = $("input[name='search']");
    const $divUsuarios = $("#divUsuarios");

    const $modal = $("#userdelModal");
    const $btnCancel = $("#userdelCancel");
    const $btnConfirm = $("#userdelConfirm");

    let userToDelete = null;
    let userActive = null;

    async function renderUsuarios(users) {
        $divUsuarios.empty();
    
        if (!users || !users.length) {
            $divUsuarios.html(`<div class="user-empty">No se encontraron usuarios</div>`);
            return;
        }
    
        for (const u of users) {
            let empresasHtml = "";
    
            if (u.productos_empresas === "all") {
                empresasHtml = "Todas las empresas";
            } else {
                let dataCompanies = await fetch_companies_code(u.productos_empresas);
                empresasHtml = Array.isArray(dataCompanies)
                    ? dataCompanies.map(c => c.name || c.company_name || c.code).join(', ')
                    : u.productos_empresas.join(', ');
            }
    
            $divUsuarios.append(`
                <div class="user-card"
                    data-id="${u.id}"
                    data-name="${u.name}"
                    data-lastname="${u.lastname}"
                    data-username="${u.username}"
                    data-active="${u.active}"
                    data-email="${u.email}"
                    data-level="${u.level}"
                    data-ip="${u.ip_user}"
                    data-empresas='${JSON.stringify(u.productos_empresas).replace(/"/g, "&quot;")}'>
                    <div class="user-title">
                        <span>${u.name} ${u.lastname}</span>
                        <small>${u.username}</small>
                    </div>
    
                    <div class="user-info">
                        <p><strong>Email:</strong> ${u.email}</p>
                        <p><strong>Nivel:</strong> ${u.level}</p>
                        <p><strong>Estado:</strong> ${u.active == "1" ? "Activo" : "Inactivo"}</p>
                        <p><strong>Empresas:</strong> <span class="empresas">${empresasHtml}</span></p>
                    </div>
    
                    <div class="user-actions">
                        <button class="user-btn-edit bg-transparent p-0 m-0 text-center">
                            <i class="material-icons text-center p-2">edit</i>
                        </button>

                        <button class="user-btn-delete bg-transparent p-0 m-0 text-center"
                            data-id="${u.id}" data-active="${u.active}">
                            <i class="material-icons p-2">
                                ${u.active == 0 ? 'block' : 'check_circle'}
                            </i>
                        </button>
                    </div>
                </div>
            `);
        }
    
        // Botón activar/desactivar
        $(".user-btn-delete").off("click").on("click", function () {
            userToDelete = $(this).data("id");
            userActive = parseInt($(this).data("active"));

            const $title = $("#userdelTitle");
            const $text = $("#userdelText");

            if (userActive === 1) {
                // Está activo → vamos a desactivar
                $title.text("Desactivar usuario");
                $text.text("¿Seguro que deseas desactivar este usuario?");
                $btnConfirm.text("Desactivar");
            } else {
                // Está inactivo → vamos a activar
                $title.text("Activar usuario");
                $text.text("¿Seguro que deseas activar este usuario?");
                $btnConfirm.text("Activar");
            }

            $modal.addClass("active");
        });
    }
    
    // cancelar modal
    $btnCancel.on("click", function () {
        $modal.removeClass("active");
        userToDelete = null;
    });

    // confirmar
    $btnConfirm.on("click", function () {
        if (!userToDelete) return;

        const newState = userActive === 1 ? 0 : 1;

        console.log("CAMBIANDO ESTADO USUARIO:", userToDelete, "=>", newState);

        fetchAPI("user", "PUT", { disabled: { id: userToDelete, module: 'users', active: newState } })
        .then(() => {
            location.reload();
        })
        .catch(() => {
            showErrorModal("Error al guardar el usuario.");
        });
    });

    let debounceTimer;

    $inputSearch.on("input", function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            search_users($(this).val().trim()).then(renderUsuarios);
        }, 300);
    });

    search_users("").then(renderUsuarios);
});
