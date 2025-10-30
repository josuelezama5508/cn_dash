<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!--  -->
        <div id="form-add-channel" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label for="channel-name" style="font-weight: 700;">Canal:</label> <span style="color: red;">*</span>
                <input type="text" name="channelname" id="channelname" class="form-control ds-input">
            </div>

            <div style="display: flex; flex-direction: row; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label for="channel-type" style="font-weight: 700;">Tipo:</label> <span style="color: red;">*</span>
                    <div id="divType"></div>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="channelmethodpay" style="font-weight: 700;">Metodo de pago:</label>
                    <input type="text" name="channelmethodpay" id="channelmethodpay" class="form-control ds-input">
                </div>
            </div>
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
        <div class="form-group">
            <button id="addRepItem" class="btn-icon" style="color: #FFF; background: #007bff; border-radius: 3px; border: none;">
                <i class="material-icons left">add</i>ADD REP
            </button>
        </div>
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
                <td><div class="form-group" style="flex: 1;"><input type="text" name="repphone[]" class="form-control ds-input" placeholder="Telefono"></div></td>
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
                    [ban, msg] = validate_data(texto, regexTextMetodoPayment);
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
                        [ban, msg] = validate_data(texto, regexPhoneRep);
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

    async function validate_existing_channel(channelName) 
    {
        if (!channelName) return false;

        try {
            const existing = await fetch_channels_by_name(channelName);

            if (existing && existing.length > 0) {
                const $input = $("#channelname");
                const $parent = $input.closest(".form-group"); // padre directo

                // limpiar cualquier mensaje previo
                $parent.find(".toast-inline").remove();

                // crear el toast encima del input
                const $msg = $(`
                    <div class="toast-inline text-white bg-danger"
                        style="position:absolute; top:0px; right:0; 
                        padding:3px 8px; border-radius:3px; 
                        font-size:12px; z-index:1000;">
                        Este canal ya existe
                    </div>
                `);

                // asegurar que el padre tenga posición relativa
                if ($parent.css("position") === "static") {
                    $parent.css("position", "relative");
                }

                $parent.append($msg);

                // animar y eliminar después
                setTimeout(() => $msg.fadeOut(300, () => $msg.remove()), 2500);

                $input.focus();
                return true;
            }

            return false;
        } catch (error) {
            console.error("Error al validar canal existente:", error);
            return false;
        }
    }




    async function sendEvent(widget = null) {
        let valid_1 = rep_items_are_valid();
        let valid_2 = channel_data_are_valid();

        if (valid_1 && valid_2) {
            const channelName = $("#channelname").val().trim();

            if (await validate_existing_channel(channelName)) {
                return; // no se guarda si existe
            }

            // Crear FormData y enviar POST como antes
            const formData = new FormData();

            $("#form-add-channel :input").each(function () {
                formData.append($(this).attr("name"), $(this).val());
            });

            $("#form-add-rep tbody tr").each(function () {
                $(this).find(":input").each(function () {
                    const name = $(this).attr("name");
                    const value = $(this).val();
                    formData.append(name, value);
                });
            });

            try {
                const response = await fetchAPI('canales', 'POST', formData);
                const status = response.status;

                if (status === 204) {
                    updateView = true;
                    location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || "Error al guardar.");
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Error de conexión.");
            }
        }
    }


    function cancelEvent(widget = null) {
        if (updateView) location.reload();
        if (widget) widget.close();
    }
</script>