var instance_of_modal = null;
var updateView = false;


$(document).ready(function() {
    $("#activandomodal").css("display", "block");
    $("#activandomodal").on("click", function() { activandomodalEvent(); });

    registered_channel('');
    $("[name='search']").on("input", function() { registered_channel($(this).val()); });
});

const registered_channel = async (condition) => {
    fetchAPI(`canales?search=${condition}`, 'GET')
      .then(async (response) => {
        const status = response.status;

        if (status == 200) {
            const text = await response.json();
            let rows = "";
            
            (text.data).forEach((element) => {
                rows += `
                    <tr style="min-height: 70px;" id="item-channle-${element.id}">
                        <td style="padding: 20px 0;">${element.name}</td>
                        <td style="padding: 20px 0;">
                            <div style="display: flex; flex-direction: row;">
                                <span class="rep-btn" style="display:inlineblock;padding:3px 7px;border-top-left-radius:3px;border-bottom-left-radius:3px;background:#444;color:#FFF;cursor:pointer;vertical-align:middle;box-shadow:1px 1px 3px 0px #111;height:35px;" id="rep-${element.id}">
                                    <i class="material-icons">group</i>
                                </span>
                                <span style="margin-left:0px;padding:0px 0px;background:#0277bd;color:#FFF;max-width:30px;width:30px;display:inline-block;text-align:center;border-top-right-radius:3px;height:35px;border-bottom-right-radius:3px;vertical-align:middle;font-size:24px;">
                                    <span>${element.totalreps}</span>
                                </span>
                            </div>
                        </td>
                        <td style="padding: 20px 0;">${element.phone}</td>
                        <td style="padding: 20px 0;">${element.type}</td>
                        <td style="padding: 20px 0;">
                            <div class="row-content-right">
                                <div class="form-group edit-btn" id="channel-${element.id}"><i class="material-icons">edit</i></div>
                                <div class="form-group delete-btn" id="channel-${element.id}"><i class="material-icons">delete</i></div>
                            </div>
                        </td>
                    </tr>`;
            });
            $("#RBuscador").html(rows);
            
            $("#RBuscador").on("click", ".rep-btn", function() { btnRepEvent(this); });
            $("#RBuscador").on("click", ".edit-btn", function() {
                let id = $(this).attr("id").split("channel-")[1];
                if (!id) return;
                paintModal("edit_channel", id);
            });
            $("#RBuscador").on("click", ".delete-btn", function() {
                let id = $(this).attr("id").split("channel-")[1];
                if (!id) return;
                btnDeleteEvent(id);
            });
        }
      })
      .catch((error) => {});
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


function btnDeleteEvent(id) {
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
                action: function () {
                    fetchAPI(`canales?id=${id}`, "DELETE")
                      .then(async (response) => {
                        const status = response.status;

                        if (status == 204) {
                            location.reload();
                        }
                      })
                      .catch((error) => {});
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
