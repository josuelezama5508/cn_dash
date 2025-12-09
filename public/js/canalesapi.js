// 🔹 Solo consulta todos los canales
async function fetch_channels() {
    try {
        const response = await fetchAPI("canales?getChannels=", "GET");
        const data = await response.json();
        if (response.ok && data.data?.length) {
            return data.data; // ✅ devuelve array de canales
        } else {
            console.warn("No se encontraron canales.");
            return [];
        }
    } catch (error) {
        console.error("Error al obtener canales:", error);
        return [];
    }
}
function render_channels(channels, highlightId = null) {
    const $channelSelect = $("#channelSelect")
        .empty()
        .append('<option value="">Selecciona un canal</option>');

    if (Array.isArray(channels) && channels.length) {
        channels.forEach(channel => {
            const $option = $(`<option value="${channel.id}">${channel.nombre}</option>`);

            // 🔹 Resaltar si coincide con el highlightId
            if (highlightId && channel.id == highlightId) {
                $option.css({
                    "background-color": "#d1ffd1",
                    "font-weight": "bold"
                });
            }

            $channelSelect.append($option);
        });
    }

    $channelSelect.append('<option value="add">➕ Agregar</option>');
    if (highlightId) {
        $channelSelect.val(highlightId).trigger("change");
    }
    
}


$(document).on("change", "#channelSelect", async function () {
    const val = $(this).val();

    if (val === "add") {
        $(this).val(""); // reset selection
        render_add_channel_form(); // 👈 Aquí renderizas el formulario directamente
        
    } else {
        $("#channelFormContainer").empty(); // limpia el formulario si se cambia a otra opción
        if (val) {
            const reps = await fetch_reps(val);
            render_reps(reps);
        }
    }
});
function render_add_channel_form() {
    const html = `
        <section style="padding: 4px; border: 1px solid #ccc; border-radius: 8px;">
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div id="form-add-channel" style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <div class="form-group">
                        <label for="channel-name" style="font-weight: 700;">Canal: <span style="color: red;">*</span></label>
                        <input type="text" name="channelname" id="channel-name" class="form-control ds-input">
                    </div>
                </div>

                

                <div class="form-group">
                    <button id="addRepItem" class="btn-icon" type="button" style="color: #FFF; background: #007bff; border-radius: 3px; border:none;">
                        <i class="material-icons left">add</i> ADD REP
                    </button>
                </div>

                <form id="form-add-rep" style="display:none;">
                   
                    <table class="table table-scrollbar" style="margin: 0;">
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="addRep"></tbody>
                    </table>
                </form>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button class="btn btn-green" id="btnSaveChannel">Guardar canal</button>
                    <button class="btn btn-red" id="btnCancelChannel">Cancelar</button>
                </div>
            </div>
        </section>
    `;
    $("#channelFormContainer").html(html);
    $("#divType").html(create_channel_type());
}

$(document).on("click", "#btnSaveChannel", function (e) {
    e.preventDefault();
    sendEvent(); // ya existente
});

$(document).on("click", "#btnCancelChannel", function (e) {
    e.preventDefault();
    $("#channelFormContainer").empty();
});

function create_rep_item() {
    const companycode = $("#companySelect").val();
    if (!companycode || companycode === "0") {
        alert("Por favor selecciona una empresa antes de agregar repsssss.");
        return;
    }

    let isValid = rep_items_are_valid();
    if (!isValid) return;

    let count = itemProductCount;
    let item = `
        <tr class="rep-item-${count}">
            <td><input type="text" name="repname[]" class="form-control ds-input" placeholder="Nombre"></td>
            <td><div class="delete-btn delete-rep"><i class="material-icons">cancel</i></div></td>
        </tr>`;
    itemProductCount++;
    $("#addRep").append(item);
}

const registered_channel = async (condition) => {
    fetchAPI(`canales?search=${condition}`, 'GET')
      .then(async (response) => {
        if (response.status !== 200) return;
        const text = await response.json();
        let rows = "";

        text.data.forEach((element) => {
            rows += `
                <tr style="min-height: 70px;" id="item-channle-${element.id}">
                    <td style="padding: 20px 0;">${element.name}</td>
                    <td style="padding: 20px 0;">
                        <div style="display: flex; flex-direction: row;">
                            <span class="rep-btn" id="rep-${element.id}">
                                <i class="material-icons">group</i>
                            </span>
                            <span>${element.totalreps}</span>
                        </div>
                    </td>
                    <td>${element.phone}</td>
                    <td>${element.type}</td>
                    <td>
                        <div class="edit-btn" id="channel-${element.id}"><i class="material-icons">edit</i></div>
                        <div class="delete-btn" id="channel-${element.id}"><i class="material-icons">delete</i></div>
                    </td>
                </tr>`;
        });

        // ✅ fila con botón "Agregar" solo si allowAddChannel = true
        if (allowAddChannel) {
            rows += `
                <tr>
                    <td colspan="5" style="text-align:center; padding:15px;">
                        <button class="btn-add-channel btn btn-success">
                            <i class="material-icons">add</i> Modulo para crear canal
                        </button>
                    </td>
                </tr>`;
        }

        $("#RBuscador").html(rows);
    })
    .catch((error) => { console.error(error); });
};
// 🔹 Tu función de modal (reutilizada tal cual)
function activandomodalEvent() {
    if (instance_of_modal && instance_of_modal.isOpen) {
        instance_of_modal.close();
        instance_of_modal = null;
    }

    instance_of_modal = $.confirm({
        title: 'Modulo para craer canal',
        content: `url:${window.url_web}/form/add_channel`,
        boxWidth: "900px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    if (typeof sendEvent === 'function') sendEvent(instance_of_modal);
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {
                    if (typeof cancelEvent === "function") {
                        cancelEvent(instance_of_modal);
                    } else {
                        return true;
                    }
                    return false;
                }
            }
        }
    });
}
// 🔹 Consulta un canal por ID
async function fetch_channelById(channelId) {
    try {
        const response = await fetchAPI(`canales?channelid=${channelId}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : null;
    } catch (error) {
        console.error("Error al obtener canal:", error);
        return null;
    }
}
// 🔹 Consulta reps de un canal
async function fetch_reps(channelId) {
    try {
        const response = await fetchAPI(`canales?getReps=${channelId}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : [];
    } catch (error) {
        console.error("Error al obtener reps:", error);
        return [];
    }
}
// 🔹 Pinta reps en el select (con opción fija "Agregar")
function render_reps(reps) {
    const $repSelect = $("#repSelect")
        .empty()
        .append('<option value="">Selecciona un representante</option>');
    if (Array.isArray(reps) && reps.length) {
        reps.forEach(rep => {
            $repSelect.append(`<option value="${rep.id}">${rep.nombre}</option>`);
        });
    } else {
        $repSelect.append('<option value="">No hay representantes</option>');
    }
    // ✅ opción fija "Agregar"
    $repSelect.append('<option value="add">➕ Agregar</option>');
}
// 🔹 Evento para detectar si eligen "Agregar"
// Evento cambio en reps: si eligen "Agregar" mostrar form inline en repFormContainer
// Si usas Select2
$('#repSelect').on('select2:select', function (e) {
    const val = e.params.data.id;
    console.log("🎯 select2 seleccionado:", val);

    if (val === "add") {
        $(this).val("").trigger('change');

        const channelId = $("#channelSelect").val();
        if (!channelId) {
            ReservationValidator.validateChannel('#channelSelect');

            // alert("Primero selecciona un canal para agregar representantes.");
            return;
        }

        render_add_rep_form(channelId);
    }
});
$(document).on("click", "#addRepItem", function(e) {
    create_rep_item(e);
    
});
// 🔹 Validación continua para #channel-name
$(document).on("input change", "#channel-name", function () {
    const value = $(this).val();
    const validation = validateFieldById("comentario", value);

    if (!validation.valid) {
        $(this).addClass("input-error");
    } else {
        $(this).removeClass("input-error");
    }
});

function create_rep_item(e) {
    const companycode = $("#channelSelect").val();     // SELECT
    const channelname = $("#channel-name").val();      // INPUT

    const isCompanySelected = companycode && companycode !== "0";
    const channelValidation = validateFieldById("comentario", channelname);

    let allowAdd = true;

    // ✅ Validar si ya hay repname[] existentes, y si son válidos
    $("input[name='repname[]']").each(function () {
        const val = $(this).val().trim();
        const validation = validateFieldById("comentario", val);

        if (!val || !validation.valid) {
            $(this).addClass("input-error");
            allowAdd = false;
        } else {
            $(this).removeClass("input-error");
        }
    });

    // ❌ Si no se cumple ninguna condición válida, no continuar
    if (!isCompanySelected && (!channelname || !channelValidation.valid) && !allowAdd) {
        $("#channel-name").addClass("input-error");
        return;
    }

    // ❌ Si ya hay repname[] inválido, no agregar otro
    if (!allowAdd) {
        return;
    }

    e.preventDefault();

    $("#form-add-rep").slideDown();

    let count = itemProductCount;
    let item = `
        <tr class="rep-item-${count}">
            <td>
                <input type="text" name="repname[]" class="form-control ds-input"
                    placeholder="Nombre" onchange="validateRepInput(this)">
            </td>
            <td>
                <div class="delete-btn delete-rep"><i class="material-icons">cancel</i></div>
            </td>
        </tr>`;
    itemProductCount++;
    $("#addRep").append(item);
}


// Función para pintar formulario para agregar reps dentro de repFormContainer
function render_add_rep_form(channelId) {
    const html = `
        <section style="border: 1px solid #ccc; padding: 15px; border-radius: 8px; margin-top: 15px;">
            <h4>Agregar representante</h4>
            <form id="formAddRepInline">
                <div class="form-group">
                    <label>Nombre <span style="color:red;">*</span></label>
                    <input type="text" id="repNombreInline" class="form-control" required />
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="repTelefonoInline" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="repEmailInline" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Comisión</label>
                    <input type="number" step="0.01" id="repComisionInline" class="form-control" />
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                    <button type="submit" class="btn btn-green">Guardar</button>
                    <button type="button" id="cancelAddRepInline" class="btn btn-red">Cancelar</button>
                </div>
            </form>
        </section>
    `;
    $("#repFormContainer").html(html);

    // Guardamos el channelId para usarlo en el submit
    $("#formAddRepInline").data("channelId", channelId);
}

// Evento submit para guardar rep inline
$(document).on("submit", "#formAddRepInline", async function (e) {
    e.preventDefault();

    const channelId = $(this).data("channelId");
    const repname = $("#repNombreInline").val().trim();

    if (!repname) {
        alert("El nombre es obligatorio.");
        return;
    }

    try {
        const response = await fetchAPI("rep", "POST", {
            repname,
            repphone,
            repemail,
            repcommission,
            channelid: channelId
        });
        const data = await response.json();
        if (response.ok) {
            // Refrescar reps para el canal
            const updatedReps = await fetch_reps(channelId);
            render_reps(updatedReps);
            $("#repFormContainer").empty(); // limpiar form inline
            $("#repSelect").val(data.id); // seleccionar nuevo rep
        } else {
            alert("Error al guardar representante.");
        }
    } catch (err) {
        console.error("Error al guardar rep:", err);
        alert("Error de conexión.");
    }
});

// Cancelar agregar rep inline
$(document).on("click", "#cancelAddRepInline", function () {
    $("#repFormContainer").empty();
});

// 🔹 Modal para agregar reps
function activandoModalRep(channelId) {
    if (instance_of_modal && instance_of_modal.isOpen) {
        instance_of_modal.close();
        instance_of_modal = null;
    }
    instance_of_modal = $.confirm({
        title: 'Nuevo representante',
        content: `
            <form id="formAddRep" class="formName">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="repNombre" class="form-control" required />
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="repTelefono" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="repEmail" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Comisión</label>
                    <input type="number" step="0.01" id="repComision" class="form-control" />
                </div>
            </form>
        `,
        boxWidth: "600px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Guardar",
                btnClass: "btn-green",
                action: async () => {
                    const repname = $("#repNombre").val().trim();
                    const repphone = $("#repTelefono").val().trim();
                    const repemail = $("#repEmail").val().trim();
                    const repcommission = $("#repComision").val().trim();

                    if (!repname) {
                        $.alert("El nombre es obligatorio.");
                        return false;
                    }
                    try {
                        const response = await fetchAPI("rep", "POST", {
                            repname,
                            repphone,
                            repemail,
                            repcommission,
                            channelid: channelId
                        });
                        const data = await response.json();
                        if (response.ok) {
                            // refrescar reps
                            const updatedReps = await fetch_reps(channelId);
                            render_reps(updatedReps);
                            $("#repSelect").val(data.id); // seleccionar nuevo
                        } else {
                            $.alert("Error al guardar representante.");
                            return false;
                        }
                    } catch (err) {
                        console.error("Error al guardar rep:", err);
                        $.alert("Error de conexión.");
                        return false;
                    }
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red"
            }
        }
    });
}
// 🔹 Consulta un rep específico
async function fetch_repById(repId) {
    try {
        const response = await fetchAPI(`canales?getRepById=${repId}`, "GET");
        const data = await response.json();
        return response.ok ? data.data : null;
    } catch (error) {
        console.error("Error al obtener rep:", error);
        return null;
    }
}
// 🔹 Pinta un canal y rep en el resumen
function render_channelName(channel) {
    $("#PrintChannel").text(channel?.name || "N/A");
}
function render_repName(rep) {
    $("#PrintRep").text(rep?.nombre || "N/A");
}
async function fetch_channels_by_name(name) {
    try {
        const response = await fetchAPI(`canales?getChannelsByName=${encodeURIComponent(name)}`, "GET");
        const data = await response.json();

        // Normalizamos la data para que siempre sea un array
        if (response.ok && data.data) {
            return Array.isArray(data.data) ? data.data : [data.data];
        } else {
            console.warn("No se encontraron canales.");
            return [];
        }
    } catch (error) {
        console.error("Error al obtener canales:", error);
        return [];
    }
}
