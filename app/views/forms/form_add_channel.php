<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!--  -->
        <div id="form-add-channel" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label for="channel-name" style="font-weight: 700;">Canal:</label> <span style="color: red;">*</span>
                <input type="text" name="channelname" id="channel-name" class="form-control ds-input">
            </div>

            <div style="display: flex; flex-direction: row; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label for="channel-type" style="font-weight: 700;">Tipo:</label> <span style="color: red;">*</span>
                    <div id="divType"></div>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="channel-name" style="font-weight: 700;">Metodo de pago:</label>
                    <input type="text" name="channelmethodpay" id="channel-phone" class="form-control ds-input">
                </div>
            </div>
        </div>
        <!--  -->
        <div class="form-group">
            <button id="addRepItem" class="btn-icon">
                <i class="material-icons left">add</i>ADD REP
            </button>
        </div>
        <!--  -->
        <form id="form-add-rep">
            <table class="table table-scrollbar" style="margin: 0;">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col" style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="addRep"></tbody>
            </table>
        </form>
        <!--  -->
    </div>
</section>


<script>
    var itemProductCount = 0;
    var updateView = false;


    $(document).ready(function() {
        $("#divType").html(create_channel_type());

        $(document).on("click", "#addRepItem", function() {
            create_rep_item();
        });
        $(document).on("click", ".delete-rep", function() {
            remove_rep_item(this);
        });
    });


    function create_rep_item() {
        let isValid = rep_items_are_valid();
        if (!isValid) return;

        let count = itemProductCount;

        let item = `
            <tr class="rep-item-${count}">
                <td><div class="form-group" style="flex: 1;"><input type="text" name="repname[]" class="form-control ds-input" placeholder="Nombre"></div></td>
                <td><div class="form-group" style="flex: 1;"><input type="text" name="repemail[]" class="form-control ds-input" placeholder="Email"></div></td>
                <td><div class="form-group" style="flex: 1;"><input type="number" name="repphone[]" class="form-control ds-input" placeholder="Metodo de pago"></div></td>
                <td><div class="form-group" style="flex: 1;"><input type="number" name="repcommission[]" class="form-control ds-input" placeholder="Comisión"></div></td>
                <td>
                    <div class="form-group delete-btn delete-rep" style="width: 32px; height: 32px;"><i class="material-icons">cancel</i></div>
                </td>
            </tr>`;
        itemProductCount++;
        $("#addRep").append(item)
    }


    function channel_data_are_valid() {
        function test(input) {
            let ban, msg;
            let campo = $(input).attr("name");
            let texto = $(input).val();

            switch (campo) {
                case 'channelname':
                    [ban, msg] = validate_data(texto, regexName);
                    break;
                case 'channeltype':
                    [ban, msg] = validate_data(texto, regexChannelType);
                    break;
                case 'channelmethodpay':
                    [ban, msg] = validate_data(texto, regexTextArea);
                    if (texto.length == 0)
                        ban = "correcto";
                    break;
            }

            return result_validate_data(input, campo, ban, msg);
        }

        let booleanArray = [];
        $("#form-add-channel :input").each(function() {
            let boolean = test(this); // Ejecuta la validación
            booleanArray.push(boolean);
        });

        return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
    }


    function rep_items_are_valid() {
        function test(input) {
            let ban, msg;
            let campo = $(input).attr("name");
            let texto = $(input).val();

            switch (campo) {
                case 'repname[]':
                    [ban, msg] = validate_data(texto, regexName);
                    break;
                case 'repemail[]':
                    if (texto === "") {
                        ban = "correcto"; // permite vacío
                        msg = "";
                    } else {
                        [ban, msg] = validate_data(texto, regexEmail);
                    }
                    break;
                case 'repphone[]':
                    if (texto === "") {
                        ban = "correcto"; // permite vacío
                    } else {
                        [ban, msg] = validate_data(texto, regexTextArea);
                    }
                    break;
                case 'repcommission[]':
                    [ban, msg] = validate_data(texto, regexCommission);
                    break;
            }

            return result_validate_data(input, campo, ban, msg);
        }

        let rowQuantity = $("#addRep tr").length;
        if (rowQuantity == 0) return true;

        let booleanArray = [];
        $("#addRep :input").each(function() {
            let boolean = test(this); // Ejecuta la validación
            booleanArray.push(boolean);
        });

        return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
    }


    function remove_rep_item(item) {
        let className = $(item).closest("tr").attr("class"); // Obtener la clase del <tr>
        if (className) {
            let elementos = $("tr." + className); // Seleccionar todos los <tr> con la misma clase
            elementos.fadeOut(500); // Ocultar todos juntos

            setTimeout(function() {
                elementos.remove(); // Eliminarlos simultáneamente
            }, 500); // Esperar a que termine la animación
        }
    }


    function sendEvent(widget = null) {
        let valid_1 = rep_items_are_valid();
        let valid_2 = channel_data_are_valid();

        if (valid_1 && valid_2) {
            // Crear FormData manualmente
            const formData = new FormData();

            // Agregar datos del canal
            $("#form-add-channel :input").each(function () {
                formData.append($(this).attr("name"), $(this).val());
            });

            // Agregar reps como arrays
            $("#form-add-rep tbody tr").each(function () {
                $(this).find(":input").each(function () {
                    const name = $(this).attr("name"); // repname[], etc.
                    const value = $(this).val();
                    formData.append(name, value); // se mantiene como array
                });
            });

            fetchAPI('canales', 'POST', formData)
                .then(async (response) => {
                    const status = response.status;

                    if (status == 204) {
                        updateView = true;
                        location.reload();
                    } else {
                        const error = await response.json();
                        alert(error.message || "Error al guardar.");
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("Error de conexión.");
                });
        }
    }



    function cancelEvent(widget = null) {
        if (updateView) location.reload();
        if (widget) widget.close();
    }
</script>