<section style="padding: 4px;">
    <form id="form-company-product" style="display: flex; flex-direction: column; gap: 8px;">
        <!--  -->
        <div style="display: flex; flex-direction: row; align-items: center; gap: 8px;">
            <div style="width: 48px; height: 48px; padding: 0;">
                <img id="logocompany" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="form-group" style="width: 100%;" id="divCompany"></div>
        </div>
        <!--  -->
        <div class="form-group" style="width: 100%;" id="divProducts"></div>
        <!--  -->
    </form>
</section>


<script>
    $(document).ready(function() {
        create_select_widget("companies", "company", 0, "divCompany");

        selected_company();
        $(document).on("change", "[name='company']", function() {
            selected_company();
        });
    });


    function create_select_widget(category, name, selected, div) {
        $.ajax({
            url: `${window.url_web}/widgets/`,
            type: "POST",
            data: {
                widget: "select",
                category: category,
                name: name,
                search: selected,
                'id_user': window.userInfo.user_id
            },
            success: async function(response) {
                $(`#${div}`).html(response);
            },
        });
    }


    function selected_company() {
        let option = $(`[name='company'] option:selected`);
        $("#logocompany").attr({
            "src": option.length ? option.attr("data-logo") : 'http://localhost/cn_dash/public/img/no-fotos.png',
            "alt": option.length ? option.attr("data-alt") : 'No icon',
        });

        create_select_widget("products", "product", option.length ? option.val() : 0, "divProducts");
    }


    function sendEvent(widget = null) {
        function are_valid() {
            function test(input) {
                let ban, msg;
                let campo = $(input).attr("name");
                let texto = $(input).val();

                switch (campo) {
                    case 'company':
                        [ban, msg] = validate_data(texto, regexInt);
                        if (texto == '0')
                            ban = "vacio";
                        break;
                    case 'product':
                        [ban, msg] = validate_data(texto, regexInt);
                        if (texto == '0')
                            ban = "vacio";
                        break;
                }

                return result_validate_data(input, campo, ban, msg);
            }

            let booleanArray = [];
            $("#form-company-product :input").each(function() {
                let boolean = test(this);
                booleanArray.push(boolean);
            });

            return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
        }

        let valid = are_valid();
        if (!valid) return;

        let company = $(`[name='company'] option:selected`).val();
        let product = $(`[name='product'] option:selected`).val();
        window.location.href = `${window.url_web}/datos-reserva/create/${company}/${product}`;
    }
</script>