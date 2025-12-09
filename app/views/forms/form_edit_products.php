<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!--  -->
        <input type="hidden" name="productid" value="<?= $params['id'] ?>">
        <!--  -->
        <div class="form-group">
            <label style="font-weight: 700;">Producto:</label>
            <div style="margin-top: 6px;">
                <span class="form-control ds-input" id="productname"></span>
            </div>
        </div>
        <!--  -->
        <form id="form-edit-product" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div class="form-group">
                    <label style="font-weight: 700;">Tipo de producto:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divProducttype"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Idioma:</label>
                    <div style="margin-top: 6px;">
                        <span class="form-control ds-input" id="language"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Show dash:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divShowdash"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Show web:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divShowweb"></div>
                </div>
            </div>
            <!--  -->
            <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px;">
                <div class="form-group">
                    <label style="font-weight: 700;">Precio adulto:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divPriceadult"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Precio menores:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divPricechild"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Precio rider:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divPricerider"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Precio foto:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divPricephoto"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Precio wetsuit:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divPricewetsuit"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Denominación:</label> <span style="color: red;">*</span>
                    <div style="margin-top: 6px;" id="divDenomination"></div>
                </div>
                

            </div>
            <div style="width: 100%;">
                <div class="form-group">
                    <label style="font-weight: 700;">Descripción:</label>
                    <div class="form-control ds-input">
                        <textarea type="text" name="description" id="txtareaDescription" class="form-control" style="min-height: 60px;"></textarea>
                    </div>
                </div>
            </div>
        </form>
        <!--  -->
        
        <!--  -->
    </div>
</section>


<script>
    var updateView = false;
    var uploadScreen;


    $(document).ready(function() {
        registered_product();
    });


    function registered_product() {
        function create_select(name, category, id, div) {
            console.log(`${window.url_web}/widgets/`);
            $.ajax({
                url: `${window.url_web}/widgets/`,
                type: 'POST',
                data: {
                    'widget': 'select',
                    'category': category,
                    'name': name,
                    'selected_id': id,
                    'id_user': window.userInfo.user_id
                },
                success: function(response) {
                    $(div).html(response)
                }
            });
        }

        let condition = $("[name='productid']").val();
        fetchAPI(`products?id=${condition}`, 'GET')
            .then(async (response) => {
                const status = response.status;
                const result = await response.json();

                if (status == 200) {
                    let data = result.data?.data ?? result.data;
                    $("#productname").html(data.productname);
                    create_select('producttype', 'producttype', data.producttype, '#divProducttype');
                    $("#language").html(data.language);
                    create_select('showdash', 'show', data.showdash, "#divShowdash");
                    create_select('showweb', 'show', data.showweb, "#divShowweb");
                    create_select('adultprice', 'prices', data.adultprice, "#divPriceadult");
                    create_select('childprice', 'prices', data.childprice, "#divPricechild");
                    create_select('riderprice', 'prices', data.riderprice, "#divPricerider");
                    create_select('photoprice', 'prices', data.photoprice, "#divPricephoto");
                    create_select('wetsuitprice', 'prices', data.wetsuitprice, "#divPricewetsuit");
                    create_select('denomination', 'denomination', data.denomination, "#divDenomination");
                    const desc = data.description ?? "";
                    $("#txtareaDescription").val(desc);

                    // Bloquear el campo si ya trae texto
                    if (desc.trim() !== "") {
                        $("#txtareaDescription").prop("readonly", true);
                    } else {
                        $("#txtareaDescription").prop("readonly", false);
                    }
                } else {
                    $("form-edit-product").html('');
                }
            })
            .catch(error => {});
    }


    function sendEvent(widget = null) {
        let isValid = product_data_are_valid();
        if (!isValid) return;

        uploadScreen = upload_screen("Espere", "Actualizando datos del producto...");

        let id = $("[name='productid']").val();
        let formData = new FormData(document.getElementById("form-edit-product"));
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        fetchAPI(`products?id=${id}`, 'PUT', formData)
            .then(async (response) => {
                const status = response.status;
                const result = await response.json();

                if (status == 200) {
                    setTimeout(() => {
                        updateView = true;
                        uploadScreen.close();
                        if (updateView) location.reload();
                    }, 900);
            }
        });
    }


    function product_data_are_valid() {
        function test(input) {
            let ban = '';
            let msg = '';
            let campo = $(input).attr("name");
            let texto = $(input).val();

            switch (campo) {
                case 'producttype':
                    [ban, msg] = validate_data(texto, regexProductType);
                    break;
                case 'showdash':
                    [ban, msg] = validate_data(texto, regexID);
                    break;
                case 'showweb':
                    [ban, msg] = validate_data(texto, regexID);
                    break;
                case 'adultprice':
                    [ban, msg] = validate_data(texto, regexPrice);
                    break;
                case 'childprice':
                    [ban, msg] = validate_data(texto, regexPrice);
                    break;
                case 'riderprice':
                    [ban, msg] = validate_data(texto, regexPrice);
                    break;
                case 'photoprice':
                    [ban, msg] = validate_data(texto, regexPrice);
                    break;
                case 'wetsuitprice':
                    [ban, msg] = validate_data(texto, regexPrice);
                    break;
                case 'denomination':
                    [ban, msg] = validate_data(texto, regexID);
                    break;
                case 'description':
                    [ban, msg] = validate_data(texto, regexTextArea);
                    if (texto.length == 0) ban = "correcto"; // permite vacío
                    break;

            }

            return result_validate_data(input, campo, ban, msg);
        }

        let booleanArray = [];
        $("#form-edit-product :input").each(function() {
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