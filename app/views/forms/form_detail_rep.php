<style>
    .form-show-rep,
    .form-edit-rep {
        position: absolute;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.5s ease-in-out;
    }

    .form-edit-rep {
        background-color: #FFF;
        transform: translateY(100%);
        z-index: 20;
    }

    .form-edit-rep.mostrar {
        transform: translateY(0%);
    }
</style>


<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <input type="hidden" name="channelid" value="<?= $params['channelid'] ?>">
        <!--  -->
        <div style="width: 100%; display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px;" id="FReps"></div>
        <!--  -->
        <div style="min-height: 10px;" id="FDetailRep"></div>
        <!--  -->
        <div style="display: flex; flex-direction: row; gap: 10px;">
            <button id="FAddRepItem" class="btn-icon" style="z-index: 10;"><i class="material-icons left">add</i>ADD REP</button>
        </div>
        <!--  -->
        <table class="table table-scrollbar" style="margin: 0;">
            <thead>
                <tr>
                    <th scope="col" style="border: none;"></th>
                    <th scope="col" style="border: none;"></th>
                    <th scope="col" style="border: none;"></th>
                    <th scope="col" style="border: none;"></th>
                    <th scope="col" style="border: none; width: 80px;"></th>
                </tr>
            </thead>
            <tbody id="FNewReps"></tbody>
        </table>
        <!--  -->
    </div>
</section>


<script>
    var itemCount = 0;
    var updateView = false;

    $(document).ready(function() {
        registered_reps();
        $(document).on("click", "#FAddRepItem", function() {
            createRep();
        });
    });
    function fetchAPI(endpoint, method = "GET", formData = null) {
        let token = localStorage.getItem("__token");

        let bodyData = DataForAPI(method, formData);

        let options = {
            method,
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            cache: "default",
        };

        if (method !== "GET") {
            if (bodyData instanceof FormData) {
                options.body = bodyData;
                // No agregues 'Content-Type' — fetch lo hace automáticamente
            } else {
                options.body = JSON.stringify(bodyData);
                options.headers['Content-Type'] = 'application/json';
            }
        }

        return fetch(`${window.url_web}/api/${endpoint}`, options);
    }
    var registered_reps = async () => {
        let condition = $("[name='channelid']").val();
        fetchAPI(`rep?channelid=${condition}`, 'GET')
            .then(async (response) => {
                const status = response.status;

                if (status == 200) {
                    const text = await response.json();
                    let rows = '';

                    (text.data).forEach(element => {
                        rows += `
                        <span class="chip" style="background: #53e659; color: #FFF; display: flex; flex-direction: row; align-items: center; align-items: center;  gap: 10px" id="rep-${element.id}">
                            <label class="detail-rep" style="cursor: pointer;">${element.name}</label><i class="close material-icons delete-rep" style="cursor: pointer;">close</i>
                        </span>`;
                    });
                    $("#FReps").html(rows);

                    $("#FReps .detail-rep").on("click", function() {
                        detailRep(this);
                    });
                    $("#FReps .delete-rep").on("click", function() {
                        deleteRep(this);
                    });
                }
            })
            .catch((error) => {});
    };

    function detailRep(item) {
        $("#FDetailRep").html('');

        let id = $(item).closest("span").attr("id").split('rep-')[1];
        if (!id) return;

        fetchAPI(`rep?repid=${id}`, "GET")
            .then(async (response) => {
                const status = response.status;

                if (status == 200) {
                    const text = await response.json();
                    let data = text.data;

                    console.log(data)

                    $("#FDetailRep").html(`
                        <div style="min-height: 160px;">
                            <div style="box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);">
                                <div class="form-show-rep">
                                    <h3 style="background: #3F51B5; color: #FFF; padding: 8px 10px; margin: 0;">${data.name}</h3>
                                    <div>
                                        <div style="width: 100%; height: 0px; display: flex; justify-content: right;">
                                            <button style="width: 40px; height: 40px; margin: 0; display: flex; justify-content: center; align-items: center; border-color: transparent; border-radius: 50px; background-color: #FF5252; color: #FFF;  top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;  box-shadow: 0 16px 28px 0 rgba(0,0,0,0.22),0 25px 55px 0 rgba(0,0,0,0.21);">
                                                <i class="material-icons">edit</i>
                                            </button>
                                        </div>
                                        <div style="padding: 8px 10px;  flex: 1; display: flex; display: flex; flex-direction: column; justify-content: space-between; gap: 6px;">
                                            <div class="row-content-left"><i style="color: #929292;" class="material-icons">email</i> ${data.email}</div>
                                            <div class="row-content-left"><i style="color: #929292;" class="material-icons">call</i> ${data.phone}</div>
                                            <div class="row-content-left"><i style="color: #929292;" class="material-icons">local_atm</i> ${data.commission}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-edit-rep" style="padding: 6px;"></div>
                            </div>
                        </div>`);

                    $("#FDetailRep").on("click", ".form-show-rep button", function() {
                        $(".form-edit-rep").addClass("mostrar");
                        $(".form-edit-rep").html(`
                            <!--h3 style="color: #FFF; padding: 8px 10px; margin: 0;">Editar Rep</h3-->
                            <div style="padding: 8px 10px;  width: 100%; display: flex; flex-direction: row; justify-content: space-between;">
                                <h3 style="margin: 0; padding: 0;">Editar Rep</h3>
                                <button class="close-form" style="width: 20px; height: 20px; background-color: transparent; border: none;">
                                    <i class="material-icons right">close</i>
                                </button>
                            </div>
                            <form id="form-edit-rep-${data.id}">
                                <div style="display: flex; flex-direction: row; gap: 10px;">
                                    <div class="form-group" style="display: flex; flex-direction: row; gap: 4px; align-items: center;">
                                        <i class="material-icons">perm_identity</i>
                                        <input type="text" name="repname" class="form-control ds-input" value="${data.name}">
                                    </div>
                                    <div class="form-group" style="display: flex; flex-direction: row; gap: 4px; align-items: center;">
                                        <i class="material-icons">email</i>
                                        <input type="text" name="repemail" class="form-control ds-input" value="${data.email}">
                                    </div>
                                    <div class="form-group" style="display: flex; flex-direction: row; gap: 4px; align-items: center;">
                                        <i class="material-icons">perm_phone_msg</i>
                                        <input type="text" name="repphone" class="form-control ds-input" value="${data.phone}">
                                    </div>
                                    <div class="form-group" style="display: flex; flex-direction: row; gap: 4px; align-items: center;">
                                        <i class="material-icons">local_atm</i>
                                        <input type="text" name="repcommission" class="form-control ds-input" value="${data.commission}">
                                    </div>
                                </div>
                            </form>
                            <div style="padding: 8px 10px;  width: 100%; display: flex; justify-content: flex-end; align-items: center;">
                                <button style="margin: 0; padding: 5px 10px;" class="updateRep" id="rep-${data.id}">
                                    Guardar
                                </button>
                            </div>`);
                    });
                    $("#FDetailRep").on("click", ".form-edit-rep .close-form", function() {
                        $(".form-edit-rep").removeClass("mostrar");
                        $(".form-edit-rep").html('');
                    });
                    $("#FDetailRep").off("click", ".updateRep").on("click", ".updateRep", function() {
                        if (!$(this).hasClass("processed")) {
                            $(this).addClass("processed");
                            updateRep(this);
                        }
                    });
                }
                console.log(status, text)
            })
            .catch((error) => {});
    }


    function updateRep(item) {
        let id = $(item).attr("id").split('rep-')[1];
        if (!id) return;

        let formid = `form-edit-rep-${id}`;
        let isValid = validate_form_data_rep(formid);
        if (!isValid) return;

        let formData = new FormData(document.getElementById(formid));
        fetchAPI(`rep?id=${id}`, "PUT", formData)
            .then(async (response) => {
                const status = response.status;
                console.log(status)

                if (status == 204) {
                    updateView = true;
                    $("#FDetailRep").html('');
                    registered_reps();
                }
            })
    }


    function deleteRep(item) {
        $("#FDetailRep").html('');

        let id = $(item).closest("span").attr("id").split('rep-')[1];
        if (!id) return;

        fetchAPI(`rep?id=${id}`, "DELETE")
            .then(async (response) => {
                const status = response.status;

                if (status == 204) {
                    updateView = true;
                    registered_reps();
                }
            })
            .catch((error) => {});
    }


    function createRep() {
        $("#FDetailRep").html('');

        // let isValid = validate_rep();
        // if (!isValid) return;

        $("#FNewReps").append(`
            <tr class="rep-item-${itemCount}">
                <td><div class="form-group"><input type="text" name="repname[]" id="rep-name" class="form-control ds-input" placeholder="Nombre"></div></td>
                <td><div class="form-group"><input type="text" name="repemail[]" id="rep-email" class="form-control ds-input" placeholder="Email"></div></td>
                <td><div class="form-group"><input type="text" name="repphone[]" id="rep-phone" class="form-control ds-input" placeholder="Teléfono"></div></td>
                <td><div class="form-group"><input type="text" name="repcommission[]" id="rep-commission" class="form-control ds-input" placeholder="Comisión"></div></td>
                <td>
                    <div class="row-content-right" style="gap: 6px;">
                        <div class="form-group done-btn" style="width: 32px; height: 32px;"><i class="material-icons">done</i></div>
                        <div class="form-group delete-btn" style="width: 32px; height: 32px;"><i class="material-icons">cancel</i></div>
                    </div>
                </td>
            </tr>`);
        itemCount++;

        $("#FNewReps").on("click", ".done-btn", function() {
            if (!$(this).hasClass("processed")) {
                $(this).addClass("processed");

                let className = $(this).closest("tr").attr("class"); // Obtener la clase del <tr>
                let isValid = validate_form_add_rep();
                if (!isValid) return;

                let formData = new FormData();
                formData.append("channelid", $("[name='channelid']").val())
                $(`.${className} :input`).each(function() {
                    let field = $(this).attr("name");
                    let text = $(this).val();
                    formData.append(field, text);
                });

                fetchAPI("rep", "POST", formData)
                    .then(async (response) => {
                        const status = response.status;
                        // const text = await response.json();

                        if (status == 201) {
                            let elementos = $("tr." + className); // Seleccionar todos los <tr> con la misma clase
                            elementos.fadeOut(500); // Ocultar todos juntos

                            setTimeout(function() {
                                elementos.remove(); // Eliminarlos simultáneamente
                            }, 500); // Esperar a que termine la animación

                            registered_reps();
                            updateView = true;
                        }
                    })
                    .catch((error) => {});
            }
        });
        $("#FNewReps").on("click", ".delete-btn", function() {
            let className = $(this).closest("tr").attr("class"); // Obtener la clase del <tr>
            if (className) {
                let elementos = $("tr." + className); // Seleccionar todos los <tr> con la misma clase
                elementos.fadeOut(500); // Ocultar todos juntos

                setTimeout(function() {
                    elementos.remove(); // Eliminarlos simultáneamente
                }, 500); // Esperar a que termine la animación
            }
        });
    }

    function validate_form_add_rep() {
        if ($("#FNewReps tr").length == 0) return true;

        let booleanArray = [];
        $("#FNewReps :input").each(function() {
            let boolean = test(this);
            booleanArray.push(boolean);
        });

        return booleanArray.every((valor) => valor === true);
    }

    function validate_form_data_rep(formid) {
        let booleanArray = [];
        $(`#${formid} :input`).each(function() {
            let boolean = test(this);
            booleanArray.push(boolean);
        });

        return booleanArray.every((valor) => valor === true);
    }

    function test(input) {
        let ban, msg;
        let field = $(input).attr("name");
        let text = $(input).val();

        switch (field) {
            case 'repname[]':
                [ban, msg] = validate_data(text, regexName);
                break;
            case 'repemail[]':
                [ban, msg] = validate_data(text, regexEmail);
                if (text.length == 0)
                    ban = "correcto";
                break;
            case 'repphone[]':
                [ban, msg] = validate_data(text, regexPhone);
                if (text.length == 0)
                    ban = "correcto";
                break;
            case 'repcommission[]':
                [ban, msg] = validate_data(text, regexInt);
                break;
            case 'repname':
                [ban, msg] = validate_data(text, regexName);
                break;
            case 'repemail':
                [ban, msg] = validate_data(text, regexEmail);
                if (text.length == 0)
                    ban = "correcto";
                break;
            case 'repphone':
                [ban, msg] = validate_data(text, regexPhone);
                if (text.length == 0)
                    ban = "correcto";
                break;
            case 'repcommission':
                [ban, msg] = validate_data(text, regexInt);
                break;
        }

        return result_validate_data(input, field, ban, msg);
    }


    function cancelEvent(widget = null) {
        if (updateView) location.reload();
        widget.close();
    }
</script>