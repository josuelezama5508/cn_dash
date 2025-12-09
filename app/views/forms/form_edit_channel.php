<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <input type="hidden" name="channelid" value="<?= $params['id'] ?>">
        <!--  -->
        <form id="form-edit-channel" style="display: flex; flex-direction: column; gap: 20px;">
            <!--  -->
            <div class="form-group">
                <label style="font-weight: 700;">Nombre de Canal/Agencia:</label> <span style="color: red;">*</span>
                <input type="text" name="channelname" class="form-control ds-input">
            </div>
            <!--  -->
            <div style="display: flex; flex-direction: row; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label style="font-weight: 700;">Tipo de Canal/Agencia:</label> <span style="color: red;">*</span>
                    <div id="divType" style="width: 100%;"></div>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label style="font-weight: 700;">Metodo de Pago:</label>
                    <input type="text" name="channelmethodpay" class="form-control ds-input">
                </div>
                <!-- <div class="form-group" style="width: 120px;">
                    <label style="font-weight: 700;">Subcanal:</label> <span style="color: red;">*</span>
                    <div id="divSubchannel" style="width: 100%;"></div>
                </div> -->
            </div>
            <div class="form-group">
                <label style="font-weight: 700;">Comision del Canal/Agencia (%)</label> <span style="color: red;">*</span>
                <input type="number" name="channelcomision" class="form-control ds-input">
            </div>
            <!--  -->
        </form>
        <!--  -->
    </div>
</section>


<script>
    var updateView = false;

    $(document).ready(function() {
        registered_selected_channel();
    });

    function registered_selected_channel() {
        let condition = $("[name='channelid']").val();

        fetchAPI(`canales?getById=${condition}`, 'GET')
            .then(async (response) => {
                const status = response.status;
                const text = await response.json();

                if (status == 200) {
                    let data = text.data;

                    $("#divType").html(create_channel_type(data.type));
                    $("[name='channelname']").val(data.nombre);
                    $("[name='channelmethodpay']").val(data.metodopago);
                    $("[name='channelcomision']").val(data.comision);
                    // $("#divSubchannel").html(create_channel_subchannel(data.subchannel));
                }
            })
            .catch((error) => {});
    }

    function sendEvent(widget = null) {
        let isValid = channel_data_are_valid();
        if (!isValid) return;

        let condition = $("[name='channelid']").val();
        let formData = new FormData(document.getElementById("form-edit-channel"));

        fetchAPI(`canales?id=${condition}`, 'PUT', formData)
            .then(async (response) => {
                const status = response.status;
                // const text = await response.json();

                if (status == 204) {
                    updateView = true;
                    location.reload();
                }
            })
            .catch((error) => {});
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
                    [ban, msg] = validate_data(texto, /[a-zA-Z0-9.-]+/);
                    if (texto.length == 0)
                        ban = "correcto";
                    break;
                case 'subchannel':
                    [ban, msg] = validate_data(texto, regexSubChannel);
                    break;
                case 'channelcomision':
                    [ban, msg] = validate_data(texto, regexInt);
                    break;
            }

            return result_validate_data(input, campo, ban, msg);
        }

        let booleanArray = [];
        $("#form-edit-channel :input").each(function() {
            let boolean = test(this);
            booleanArray.push(boolean);
        });

        return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
    }


    function cancelEvent(widget = null) {
        if (updateView) location.reload();
        if (widget) widget.close();
    }
</script>