$(document).ready(function() {
    registered_promocode();

    $("[name='expirationdate']").flatpickr({
        dateFormat: "d/m/Y",
        minDate: "today",
    });

    $("#savePromocode").on("click", function(e) {
        e.preventDefault();
        update_promocode();
    });

    // Abrir modal productos
    $("#btnSelectProducts").on("click", function() {
        load_modal_products();
        $("#modalProducts").show();
    });

    // Abrir modal empresas
    $("#btnSelectCompanies").on("click", function() {
        load_modal_companies();
        $("#modalCompanies").show();
    });

    // Cerrar modales con botón cerrar o cancelar
    $(".modal .close, .modal .btn-secondary").on("click", function() {
        $(this).closest(".modal").hide();
    });

    // Guardar selección productos
    $("#btnSaveProducts").on("click", function() {
        save_selected_products();
        $("#modalProducts").hide();
    });

    // Guardar selección empresas
    $("#btnSaveCompanies").on("click", function() {
        save_selected_companies();
        $("#modalCompanies").hide();
    });
});

function registered_promocode() {
    let condition = $("[name='codeid']").val();

    fetchAPI(`promocode?id=${condition}`, 'GET')
    .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 200) {
            let data = text.data;

            $("#promocode").html(data.promocode);
            $("#beginingdate").html(data.startdate);
            $("[name='expirationdate']").val(data.enddate);
            create_status_select(data.status);
            $("[name='codediscount']").val(data.descount);

            // Parsear JSON strings
            let selectedCompanies = [];
            let selectedProducts = [];
            try {
                selectedCompanies = JSON.parse(data.companyCode);
            } catch(e) { selectedCompanies = []; }
            try {
                selectedProducts = JSON.parse(data.productsCode);
            } catch(e) { selectedProducts = []; }

            // Guardamos para usar en modales
            window.selectedCompanies = selectedCompanies;
            window.selectedProducts = selectedProducts;

            // Mostrar resumen en inputs readonly
            update_selected_display();

        } else {
            window.location.href = window.url_web + "/codigopromo";
        }
    })
    .catch((error) => {
        console.error("Error loading promocode:", error);
    });
}

function create_status_select(selected) {
    $.ajax({
        url: `${window.url_web}/widgets/`,
        type: "POST",
        data: {
            widget: "select",
            category: "status",
            name: 'codestatus',
            selected_id: selected,
            'id_user': window.userInfo.user_id
        },
        success: function (response) {
            $("#statusDiv").html(response);
        },
    });
}

// Dentro de load_modal_products() — agregamos checkbox "Seleccionar todo"
async function load_modal_products() {
    try {
        const response = await fetchAPI('products?getDataDash=1', 'GET');
        const { data } = await response.json();

        if (!data || !data.length) {
            $("#modalProductsBody").html("<p>No hay productos disponibles</p>");
            return;
        }

        let html = `
                <label class="select-all">
                    <input type="checkbox" id="selectAllProducts"> Seleccionar todo
                </label><br>
            `;


        data.forEach(p => {
            const isChecked = window.selectedProducts && window.selectedProducts.some(sp => sp.productcode === p.product_code);
            html += `
                <label>
                    <input type="checkbox" class="product-checkbox" data-code="${p.product_code}" data-name="${p.product_name}" ${isChecked ? 'checked' : ''}>
                    ${p.product_name}
                </label><br>
            `;
        });

        $("#modalProductsBody").html(html);

        // Evento para seleccionar/deseleccionar todos productos
        $("#selectAllProducts").on("change", function() {
            $(".product-checkbox").prop("checked", this.checked);
        });

        // Si algún checkbox individual se desmarca, quitar "Seleccionar todo"
        $(".product-checkbox").on("change", function() {
            if (!$(this).prop("checked")) {
                $("#selectAllProducts").prop("checked", false);
            } else if ($(".product-checkbox:checked").length === $(".product-checkbox").length) {
                $("#selectAllProducts").prop("checked", true);
            }
        });

        // Inicializar el checkbox "Seleccionar todo" según estado de checkboxes
        const allChecked = $(".product-checkbox").length === $(".product-checkbox:checked").length;
        $("#selectAllProducts").prop("checked", allChecked);

    } catch (err) {
        console.error("Error al cargar productos:", err);
        $("#modalProductsBody").html("<p>Error al cargar productos.</p>");
    }
}

// Lo mismo para empresas:
async function load_modal_companies() {
    try {
        const response = await fetchAPI('company?getDataDash=1', 'GET');
        const { data } = await response.json();

        if (!data || !data.length) {
            $("#modalCompaniesBody").html("<p>No hay empresas disponibles</p>");
            return;
        }

        let html = `
            <label class="select-all">
                <input type="checkbox" id="selectAllCompanies"> Seleccionar todo
            </label><br>
        `;


        data.forEach(c => {
            const isChecked = window.selectedCompanies && window.selectedCompanies.some(sc => sc.companycode === c.companycode);
            html += `
                <label>
                    <input type="checkbox" class="company-checkbox" data-code="${c.companycode}" data-name="${c.companyname}" ${isChecked ? 'checked' : ''}>
                    ${c.companyname}
                </label><br>
            `;
        });

        $("#modalCompaniesBody").html(html);

        // Evento para seleccionar/deseleccionar todas empresas
        $("#selectAllCompanies").on("change", function() {
            $(".company-checkbox").prop("checked", this.checked);
        });

        // Si algún checkbox individual se desmarca, quitar "Seleccionar todo"
        $(".company-checkbox").on("change", function() {
            if (!$(this).prop("checked")) {
                $("#selectAllCompanies").prop("checked", false);
            } else if ($(".company-checkbox:checked").length === $(".company-checkbox").length) {
                $("#selectAllCompanies").prop("checked", true);
            }
        });

        // Inicializar el checkbox "Seleccionar todo" según estado de checkboxes
        const allChecked = $(".company-checkbox").length === $(".company-checkbox:checked").length;
        $("#selectAllCompanies").prop("checked", allChecked);

    } catch (err) {
        console.error("Error al cargar empresas:", err);
        $("#modalCompaniesBody").html("<p>Error al cargar empresas.</p>");
    }
}

function save_selected_products() {
    let selected = [];
    $("#modalProductsBody input[type=checkbox]:checked").each(function() {
        selected.push({
            productcode: $(this).data("code"),
            productname: $(this).data("name")
        });
    });
    window.selectedProducts = selected;
    update_selected_display();
}

function save_selected_companies() {
    let selected = [];
    $("#modalCompaniesBody input[type=checkbox]:checked").each(function() {
        selected.push({
            companycode: $(this).data("code"),
            companyname: $(this).data("name")
        });
    });
    window.selectedCompanies = selected;
    update_selected_display();
}

function update_selected_display() {
    // Productos
    let prodNames = window.selectedProducts
        .map(p => (p.productname ? p.productname.trim() : ""))
        .filter(n => n.length > 0)
        .join(", ");

    // Empresas con validación para evitar error
    let compNames = window.selectedCompanies
        .map(c => (c.companyname ? c.companyname.trim() : ""))
        .filter(n => n.length > 0)
        .join(", ");

    $("#selectedProducts").val(prodNames);
    $("#selectedCompanies").val(compNames);
}
function normalizeObjectToArray(obj) {
    if (Array.isArray(obj)) return obj;
    return Object.keys(obj).map(k => obj[k]);
}

function update_promocode() {
    if (!promocode_data_are_valid()) return;

    let condition = $("[name='codeid']").val();
    let formData = new FormData(document.getElementById("form-edit-promocode"));

    formData.set("productsCode", JSON.stringify(normalizeObjectToArray(window.selectedProducts || [])));
    formData.set("companyCode", JSON.stringify(normalizeObjectToArray(window.selectedCompanies || [])));
    

    fetchAPI(`promocode?id=${condition}`, 'PUT', formData)
    .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 200) {
            location.reload();
        }
    })
    .catch((error) => {
        console.error("Error updating promocode:", error);
    });
}

function promocode_data_are_valid() {
    function test(input) {
        let ban, msg;
        let campo = $(input).attr("name");
        let texto = $(input).val();

        switch (campo) {
            case "expirationdate":
                [ban, msg] = validate_data(texto, regexDate);
                break;
            case "codestatus":
                [ban, msg] = validate_data(texto, regexInt);
                break;
            case "codediscount":
                [ban, msg] = validate_data(texto, regexInt);
                break;
            // No validamos productsCode/companyCode porque es controlado por modal
            default:
                return true;
        }

        return result_validate_data(input, campo, ban, msg);
    }

    let booleanArray = [];
    $("#form-edit-promocode :input").each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
}
