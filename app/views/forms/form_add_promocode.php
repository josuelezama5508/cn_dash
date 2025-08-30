<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <!--  -->
        <form id="form-add-promocode" style="display: flex; flex-direction: column; gap: 10px;">
            <!-- Código -->
            <div class="form-group">
                <label style="font-weight: 700;">Codigo:</label> <span style="color: red;">*</span>
                <input type="text" name="promocode" class="form-control ds-input input-promocode">
            </div>

            <!-- Fecha inicial -->
            <div class="form-group">
                <label style="font-weight: 700;">Fecha inicial:</label> <span style="color: red;">*</span>
                <input type="text" name="startdate" class="form-control ds-input" placeholder="DD/MM/YYYY HH:mm">
            </div>

            <!-- Fecha expiración -->
            <div class="form-group">
                <label style="font-weight: 700;">Fecha expiración:</label> <span style="color: red;">*</span>
                <input type="text" name="enddate" class="form-control ds-input" placeholder="DD/MM/YYYY HH:mm">
            </div>

            <!-- Descuento -->
            <div class="form-group">
                <label style="font-weight: 700;">Descuento: <strong>%</strong></label> <span style="color: red;">*</span>
                <input type="number" min="1" name="discount" class="form-control ds-input input-int">
            </div>

            <!-- Productos -->
            <div class="form-group">
                <label style="font-weight: 700;">Para Productos:</label>
                <input type="text" name="productsavailables" class="form-control ds-input" readonly style="background-color: #f9f9f9; cursor: pointer;">

            </div>

            <!-- Empresa -->
            <div class="form-group">
                <label style="font-weight: 700;">Para Empresas:</label>
                <input type="text" name="companiesavailables" class="form-control ds-input" readonly style="background-color: #f9f9f9; cursor: pointer;">
            </div>

        </form>
        <!-- Modal -->
        <div id="modal-products" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 700px; width: 100%;">
                <h4>Selecciona productos</h4>
                <div style="margin-bottom: 10px;">
                    <label>
                        <input type="checkbox" id="select-all-products">
                        Seleccionar todos
                    </label>
                </div> 
                <div id="product-list" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button id="confirm-products" class="btn btn-primary">Confirmar</button>
                <button id="close-modal" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
        <div id="modal-companies" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 700px; width: 100%;">
                <h4>Selecciona productos</h4>
                <div style="margin-bottom: 10px;">
                    <label>
                        <input type="checkbox" id="select-all-companies">
                        Seleccionar todos
                    </label>
                </div> 
                <div id="company-list" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button id="confirm-company" class="btn btn-primary">Confirmar</button>
                <button id="close-modal" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
        <!--  -->
        <div style="display: flex; flex-direction: row; gap: 10px;">
            <button id="saveProductItem" class="btn-icon"><i class="material-icons left">send</i>SAVE</button>
        </div>
        <!--  -->
        </div>
</section>


<script>
    const regexDateTime = /^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/;
    /^[1-9]\d*$/;

    let selectedProducts = []; // Array con los códigos seleccionados
    let selectedCompanies = [];

    $(document).ready(function () {
        loadProductsToModal();
        loadCompanyToModal();
        loadEnterprises();
        flatpickr("input[name='startdate'], input[name='enddate']", {
            enableTime: true,
            dateFormat: "d/m/Y H:i",
            time_24hr: true,
            minDate: "today"
        });

        $("#saveProductItem").on("click", function () {
            save_promocode();
        });

        // Al hacer clic o focus en el input abre modal y carga productos
        $("input[name='productsavailables']").on("focus click", function () {
            $("#modal-products").fadeIn(200);
           
        });

        $("#close-modal").on("click", function () {
            $("#modal-products").fadeOut(200);
        });
        $("#select-all-products").on("change", function () {
                const isChecked = $(this).is(":checked");
                $("#product-list input[type='checkbox']").prop("checked", isChecked);
            });
        // Confirmar selección
        $("#confirm-products").on("click", function () {
                // Seleccionar/Deseleccionar todos los productos
           

            const checked = $("#product-list input[type='checkbox']:checked");
            selectedProducts = [];

            checked.each(function () {
                const code = $(this).val();
                const name = $(this).parent().text().trim();

                selectedProducts.push({
                    productcode: code,
                    productname: name
                });
            });

            const productNames = selectedProducts.map(p => p.productname).join(', ');
            $("input[name='productsavailables']")
                .val(`${selectedProducts.length} producto(s) seleccionado(s)`)
                .attr("title", productNames); // Tooltip


            $("#modal-products").fadeOut(200);
        });
        $("input[name='companiesavailables']").on("focus click", function () {
            $("#modal-companies").fadeIn(200);
        });

        $("#close-modal-company").on("click", function () {
            $("#modal-companies").fadeOut(200);
        });

        $("#select-all-companies").on("change", function () {
            const isChecked = $(this).is(":checked");
            $("#company-list input[type='checkbox']").prop("checked", isChecked);
        });

        $("#confirm-company").on("click", function () {
            const checked = $("#company-list input[type='checkbox']:checked");
            selectedCompanies = [];

            checked.each(function () {
                const code = $(this).val();
                const name = $(this).parent().text().trim();
                selectedCompanies.push({ companycode: code, companyname: name });
            });

            const names = selectedCompanies.map(c => c.companyname).join(', ');
            $("input[name='companiesavailables']")
                .val(`${selectedCompanies.length} empresa(s) seleccionada(s)`)
                .attr("title", names);

            $("#modal-companies").fadeOut(200);
        });


    });

    // Cargar productos y marcar seleccionados
    const loadProductsToModal = async () => {
        try {
            const response = await fetchAPI('products?getDataDash=1', 'GET');


            const { data } = await response.json();

            const container = $("#product-list");
            container.empty();

            if (data && data.length > 0) {
                data.forEach(p => {
                    const isAlreadySelected = selectedProducts.some(sp => sp.productcode === p.product_code);
                    const checkbox = `
                        <label class="checkbox-item">
                            <input type="checkbox" value="${p.product_code}" ${isAlreadySelected ? "checked" : ""}>
                            <span>${p.product_name}</span>
                        </label>
                    `;
                    container.append(checkbox);
                });

                // Verificar si todos estaban seleccionados antes
                const allChecked = data.length > 0 && data.every(p =>
                    selectedProducts.some(sp => sp.productcode === p.product_code)
                );
                $("#select-all-products").prop("checked", allChecked);
                // Escuchar cambios individuales
                $("#product-list").on("change", "input[type='checkbox']", function () {
                    const total = $("#product-list input[type='checkbox']").length;
                    const checked = $("#product-list input[type='checkbox']:checked").length;

                    $("#select-all-products").prop("checked", total === checked);
                });

            } else {
                container.html("<p>No hay productos disponibles</p>");
            }
        } catch (err) {
            console.error("Error al cargar productos:", err);
            $("#product-list").html("<p>Error al cargar productos.</p>");
        }
    };
    const loadCompanyToModal = async () => {
        try {
            const response = await fetchAPI('company?getDataDash=1', 'GET');
            const { data } = await response.json();

            const container = $("#company-list");
            container.empty();

            if (data && data.length > 0) {
                data.forEach(c => {
                    const isAlreadySelected = selectedCompanies.some(sc => sc.companycode === c.companycode);
                    const checkbox = `
                        <label class="checkbox-item">
                            <input type="checkbox" value="${c.companycode}" ${isAlreadySelected ? "checked" : ""}>
                            <span>${c.companyname}</span>
                        </label>
                    `;
                    container.append(checkbox);
                });

                const allChecked = data.length > 0 && data.every(c =>
                    selectedCompanies.some(sc => sc.companycode === c.companycode)
                );
                $("#select-all-companies").prop("checked", allChecked);

                $("#company-list").on("change", "input[type='checkbox']", function () {
                    const total = $("#company-list input[type='checkbox']").length;
                    const checked = $("#company-list input[type='checkbox']:checked").length;
                    $("#select-all-companies").prop("checked", total === checked);
                });

            } else {
                container.html("<p>No hay empresas disponibles</p>");
            }
        } catch (err) {
            console.error("Error al cargar empresas:", err);
            $("#company-list").html("<p>Error al cargar empresas.</p>");
        }
    };
    function save_promocode() {
    if (!code_data_are_valid()) return;

    const form = document.getElementById("form-add-promocode");

    const data = {
        promocode: form.promocode.value.trim(),
        startdate: form.startdate.value.trim(),
        enddate: form.enddate.value.trim(),
        codediscount: parseInt(form.discount.value.trim()),
        products: selectedProducts,
        companies: selectedCompanies // <-- Añadido
    };


    console.log(data); // <- aquí puedes verificar la salida antes de enviarla

    fetchAPI('promocode', 'POST', data)
    .then(async (response) => {
        const resData = await response.json();
        if (response.status === 201) {
            location.reload();
        } else {
            alert("Error al guardar: " + (resData.message || ''));
        }
    })
    .catch((error) => {
        console.error("Error:", error);
    });

}


    function code_data_are_valid() {
        let isValid = true;

        $("#form-add-promocode :input").each(function () {
            const input = $(this);
            const name = input.attr("name");
            const value = input.val().trim();
            let valid = true;
            let msg = "";

            switch (name) {
                case 'promocode':
                    valid = regexPromoCode.test(value);
                    msg = "Código inválido";
                    break;
                case 'startdate':
                case 'enddate':
                    valid = regexDateTime.test(value);
                    msg = "Fecha y hora inválidas (usa DD/MM/YYYY HH:mm)";
                    break;
                case 'discount':
                    valid = regexInt.test(value);
                    msg = "Descuento debe ser un número mayor a 0";
                    break;
                case 'productsavailables':
                case 'originenterprise':
                    if (value !== "") {
                        valid = /^[\w\s\-.,()]+$/.test(value);
                        msg = "Solo texto permitido";
                    }
                    break;
            }

            if (!valid) {
                isValid = false;
                input.addClass("is-invalid");
                input.after(`<small class="text-danger">${msg}</small>`);
            } else {
                input.removeClass("is-invalid");
                input.siblings("small.text-danger").remove();
            }
        });

        return isValid;
    }
    const loadEnterprises = async () => {
        try {
            const response = await fetchAPI('company?getDispoEmpresas=', 'GET');
            const result = await response.json();
            console.log(result);
            const $enterpriseSelect = $("#originenterprise");
            $enterpriseSelect.empty();

            if (response.ok && result.data && result.data.length > 0) {
                $enterpriseSelect.append(`<option value="">Selecciona una Empresa</option>`);
                result.data.forEach(enterprise => {
                    const option = `<option value="${enterprise.companycode}">${enterprise.companyname}</option>`;
                    $enterpriseSelect.append(option);
                });
            } else {
                $enterpriseSelect.append(`<option value="">No hay empresas disponibles</option>`);
            }
        } catch (error) {
            console.error("Error al cargar empresas:", error);
            $("#originenterprise").empty().append(`<option value="">Error al cargar empresas</option>`);
        }
    };

</script>
