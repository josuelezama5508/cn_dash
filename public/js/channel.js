var instance_of_modal = null;
var updateView = false;
let allowAddChannel = false;

$(document).ready(function() {
    $("#activandomodal").css("display", "block");
    $("#activandomodal").on("click", function() { activandomodalEvent(); });
    registered_channel('');
    $("[name='search']").on("input", function() { registered_channel($(this).val()); });
    // Después de inyectar el HTML en #RBuscador
    $("#RBuscador").off("click", ".rep-btn").on("click", ".rep-btn", function() {
        btnRepEvent(this);
    });
    console.log('¿Existe #RBuscador?', $('#RBuscador').length); // Debe ser 1

});
const registered_channel = async (condition) => {
    fetchAPI(`canales?search=${condition}`, 'GET')
      .then(async (response) => {
        if (response.status === 200) {
            const text = await response.json();
            let rows = "";

            text.data.forEach((element) => {
                rows += `
                    <tr class="channel-row" id="item-channel-${element.id}">
                        <td class="channel-name">${element.name}</td>
                        <td>
                            <button class="rep-btn" id="rep-${element.id}">
                                <i class="material-icons">group</i>
                                <span class="rep-count">${element.totalreps}</span>
                            </button>
                        </td>
                        <td class="channel-phone">${element.phone || '-'}</td>
                        <td class="channel-type">${element.type}</td>
                        <td>
                            <div class="channel-actions">
                                <button class="edit-btn" id="channel-${element.id}">
                                    <i class="material-icons">edit</i>
                                </button>
                                <button class="delete-btn" id="channel-${element.id}">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;

            });

            if (allowAddChannel) {
                rows += `
                    <tr>
                        <td colspan="5" class="add-channel-cell">
                            <button id="activandomodal" class="btn btn-success neumorphic-btn">
                                <i class="material-icons">add</i> Nuevo canal
                            </button>
                        </td>
                    </tr>`;
            }

            $("#RBuscador").html(rows);

            // Eventos
            $("#RBuscador").on("click", ".rep-btn", function() {
                btnRepEvent(this);
            });
            $("#RBuscador").on("click", ".edit-btn", function() {
                const id = $(this).attr("id").split("channel-")[1];
                if (!id) return;
                paintModal("edit_channel", id);
            });
            $("#RBuscador").on("click", ".delete-btn", function() {
                const id = $(this).attr("id").split("channel-")[1];
                if (!id) return;
                btnDeleteEvent(id);
            });

            if (allowAddChannel) {
                $("#activandomodal").on("click", function() {
                    activandomodalEvent();
                });
            }
        }
      })
      .catch((error) => {
          console.error("Error fetching canales:", error);
      });
};


function btnRepEvent(input) {
    let id = ($(input).attr("id")).split('rep-')[1];
    if (!id) return;
    paintModal("show_rep", id);
}
function paintModal(version = '', id) {
    if (instance_of_modal && instance_of_modal.isOpen) {
        instance_of_modal.close();
        instance_of_modal = null;
    }
    let modal = {
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    if (typeof sendEvent === "function") sendEvent(instance_of_modal);
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
            },
        },
    };
    switch (version) {
        case "show_rep":
            modal.title = "Representantes";
            modal.boxWidth = "900px";
            modal.content = `url:${window.url_web}/form/detalles_rep?channelid=${id}`;
            delete modal.buttons.ok;
            break;
        case "edit_channel":
            modal.title = "Editar canal";
            modal.boxWidth = "600px";
            modal.content = `url:${window.url_web}/form/edit_channel?id=${id}`;
            break;
        case "delete_channel":
            modal.title = "¿Eliminar canal?";
            modal.boxWidth = "400px";
            modal.content = '';
            break;
    }
    instance_of_modal = $.confirm(modal);
    return instance_of_modal;
}
function activandomodalEvent() {
    if (instance_of_modal && instance_of_modal.isOpen) {
        instance_of_modal.close();
        instance_of_modal = null;
    }

    instance_of_modal = $.confirm({
        title: 'Nuevo canal',
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
function buildQueryParams(params) {
    const query = [];
    for (const key in params) {
        if (Array.isArray(params[key])) {
            params[key].forEach(val => {
                query.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(val)}`);
            });
        } else {
            query.push(`${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`);
        }
    }
    return query.join('&');
}
async function btnDeleteEvent(id) {
    if (instance_of_modal && instance_of_modal.isOpen) {
        instance_of_modal.close();
        instance_of_modal = null;
    }

    instance_of_modal = $.confirm({
        title: "¿Eliminar canal?",
        boxWidth: "400px",
        content: ``,
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: async function () {
                    try {
                        // Obtener reps asociados al canal
                        const responseReps = await fetchAPI(`rep?channelid=${id}`, "GET");
                        const repsJson = await responseReps.json();
                        const rep_ids = repsJson.data.map(rep => rep.id);

                        // Construir query con reps[] y id
                        const queryParams = buildQueryParams({ id: id, reps: rep_ids });

                        // Llamar al endpoint con DELETE y query string correcta
                        const deleteResponse = await fetchAPI(`canales?${queryParams}`, "DELETE");

                        if (deleteResponse.status === 204) {
                            location.reload();
                        } else {
                            const error = await deleteResponse.json();
                            console.error("Error al eliminar canal:", error.message);
                        }
                    } catch (error) {
                        console.error("Error al procesar la eliminación del canal:", error);
                    }
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {}
            },
        },
    });
}
