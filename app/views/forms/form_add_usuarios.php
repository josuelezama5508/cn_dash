<section style="padding: 4px;">
  <div style="display: flex; flex-direction: column; gap: 20px;">
    <div id="form-add-user" style="display: flex; flex-direction: column; gap: 5px;">
        
      <h3 id="user-form-title" style="margin:0;">Agregar Usuario</h3>

      <!-- Inputs -->
      <div class="form-group">
        <label for="name" style="font-weight: 700;">Nombre:</label> <span style="color: red;">*</span>
        <input type="text" id="name" class="form-control ds-input">
      </div>

      <div class="form-group">
        <label for="lastname" style="font-weight: 700;">Apellido/s:</label> 
        <input type="text" id="lastname" class="form-control ds-input">
      </div>

      <div class="form-group">
        <label for="username" style="font-weight: 700;">Usuario:</label><span style="color: red;">*</span>
        <input type="text" id="username" class="form-control ds-input">
      </div>

      <div class="form-group">
        <label for="email" style="font-weight: 700;">Correo Electronico:</label>
        <input type="text" id="email" class="form-control ds-input">
      </div>

      <div class="form-group">
        <label for="password" style="font-weight: 700;">Contraseña:</label><span style="color: red;">*</span>
        <input type="text" id="password" class="form-control ds-input">
      </div>

      <!-- Rol -->
      <div class="form-group">
        <label for="level" style="font-weight: 700;">Rol:</label> <span style="color: red;">*</span>
        <div id="select-rol"></div>
      </div>
      <!-- <div class="form-group">
        <label for="ip_user" style="font-weight: 700;">IP User:</label>
        <input type="text" id="ip_user" class="form-control ds-input">
      </div> -->

      <!-- Empresa (readonly + modal) -->
      <div class="form-group">
          <label style="font-weight: 700;">Empresa:</label> <span style="color: red;">*</span>
          <input 
              type="text" 
              name="companiesavailables" 
              id="companiesavailables" 
              class="form-control ds-input" 
              readonly 
              style="background-color: #f9f9f9; cursor: pointer;">
      </div>

      <!-- Botones -->
      <div class="form-group" style="display:flex; gap:10px;">
        <button type="button" id="saveUser" class="btn-icon">
          <i class="material-icons left">save</i> Guardar
        </button>
        <button type="button" id="cancelUser" class="btn-icon">
          <i class="material-icons left">cancel</i> Cancelar
        </button>
      </div>

    </div>
  </div>
</section>
<script>
window.userFormValid = {
    name: false,
    lastname: false,
    username: false,
    email: false,
    password: false,
    companies: false
};

$(document).ready(function () {

    let editingUserId = null;
    let selectedCompanies = [];

    function validateUser() {
        const nombre = $("#name").val().trim();
        const lastname = $("#lastname").val().trim();
        if (!nombre || !lastname) {
            alert("Nombre y apellido son obligatorios.");
            return false;
        }
        return true;
    }

    function loadSelectRol(selected = "") {
        $.ajax({
            url: `${window.url_web}/widgets/`,
            type: 'POST',
            data: {
                widget: 'select',
                category: 'rol_user',
                name: 'level',
                selected_id: selected,
                id_user: window.userInfo.user_id
            },
            success: function(response) {
                $("#select-rol").html(response);
            }
        });
    }

    function clearForm() {
        editingUserId = null;
        selectedCompanies = [];

        $("#user-form-title").text("Agregar Usuario");
        $("#name, #lastname, #username, #email, #password").val("");

        $("#companiesavailables").val("Seleccionar empresa").attr("title", "");

        loadSelectRol("reservaciones");
    }

    function fillForm(user)
    {
        editingUserId = user.id;
        oldUserData = { ...user };

        $("#user-form-title").text("Editar Usuario: " + user.name);

        $("#name").val(user.name || "");
        $("#lastname").val(user.lastname || "");
        $("#username").val(user.username || "");
        $("#email").val(user.email || "");
        $("#password").val("");

        loadSelectRol(user.level || "");

        preloadUserEnterprises(user.empresas);

        // 🔥 FIX: marcar campos como válidos al cargar el usuario
        window.userFormValid.name = true;
        window.userFormValid.lastname = true;
        window.userFormValid.username = true;
        window.userFormValid.email = true;
        window.userFormValid.password = true;
        validateSelectedCompanies();

        console.log("Validaciones al cargar user:", window.userFormValid);
    }


    function preloadUserEnterprises(empresasRaw) {
        let empresas = [];

        if (empresasRaw === "all") {
            empresas = "all";
        } else {
            try {
                empresas = JSON.parse(empresasRaw);
            } catch {
                empresas = [];
            }
        }

        if (empresas === "all") {
            $("#enterprise-all").prop("checked", true);
            $(".enterprise-check").prop("checked", true);

            $("input[name='companiesavailables']")
                .val("Todas las empresas")
                .attr("title", "Todas las empresas");

            selectedCompanies = "all";
            return;
        }

        $(".enterprise-check").each(function () {
            const code = $(this).val();
            if (empresas.includes(code)) {
                $(this).prop("checked", true);
            }
        });

        const items = empresas.length;
        const tooltip = empresas.join(", ");

        $("input[name='companiesavailables']")
            .val(`${items} empresa(s) seleccionada(s)`)
            .attr("title", tooltip);

        // 🔥 **CORREGIDO: aquí ya NO se usa JSON.stringify**
        selectedCompanies = empresas;
    }

    $(document).on("focus click", "input[name='companiesavailables']", function () {
        loadEnterprisesModal();
    });

    async function loadEnterprisesModal() {

// 🔥 SOLUCIÓN: si el usuario tenía "all", ahora al editar lo tratamos como []
if (selectedCompanies === "all") {
    selectedCompanies = [];
}

try {
    const response = await fetchAPI('company?getDispoEmpresas=', 'GET');
    const result = await response.json();

    const $list = $("#modal-enterprise-list");
    $list.empty();

    if (response.ok && result.data && result.data.length > 0) {
        $list.append(`
            <label style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #ddd; font-weight:700;">
                <input type="checkbox" id="enterprise-all">
                Todas las empresas
            </label>
        `);

        result.data.forEach(e => {
            $list.append(`
                <label style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #eee; cursor:pointer;">
                    <input 
                        type="checkbox" 
                        class="enterprise-check" 
                        value="${e.companycode}" 
                        data-name="${e.companyname}">
                    ${e.companyname}
                </label>
            `);
        });

    } else {
        $list.append(`<p>No hay empresas disponibles.</p>`);
    }

    if (editingUserId && oldUserData) {
        preloadUserEnterprises(oldUserData.empresas);
    }

    $("#modal-enterprise").css("display", "flex");

} catch (error) {
    console.error(error);
    $("#modal-enterprise-list").html(`<p>Error al cargar empresas.</p>`);
    $("#modal-enterprise").css("display", "flex");
}
}


    $("#closeEnterpriseModal, #closeEnterpriseModal2").on("click", function () {
        $("#modal-enterprise").hide();
    });

    $(document).on("change", "#enterprise-all", function () {
        const checked = this.checked;

        $(".enterprise-check").prop("checked", checked);

        selectedCompanies = checked ? "all" : [];

        $("#companiesavailables").val(checked ? "Todas las empresas" : "");

        validateSelectedCompanies();
    });

    $(document).on("change", ".enterprise-check", function () {

        if ($("#enterprise-all").is(":checked")) {
            $("#enterprise-all").prop("checked", false);
            selectedCompanies = [];
        }

        const code = $(this).val();

        if (this.checked) {
            if (!selectedCompanies.includes(code)) {
                selectedCompanies.push(code);
            }
        } else {
            selectedCompanies = selectedCompanies.filter(c => c !== code);
        }

        const selectedNames = $(".enterprise-check:checked")
            .map(function () { return $(this).data("name"); })
            .get()
            .join(", ");

        $("#companiesavailables").val(selectedNames);

        validateSelectedCompanies();
    });

    $("#saveEnterpriseSelection").on("click", function () {

        if ($("#enterprise-all").is(":checked")) {

            selectedCompanies = "all";

            $("#companiesavailables").val("Todas las empresas");

            validateSelectedCompanies();
            $("#modal-enterprise").hide();
            return;
        }

        selectedCompanies = $(".enterprise-check:checked")
            .map(function () { return $(this).val(); })
            .get();

        const selectedNames = $(".enterprise-check:checked")
            .map(function () { return $(this).data("name"); })
            .get()
            .join(", ");

        $("#companiesavailables").val(selectedNames);

        validateSelectedCompanies();
        $("#modal-enterprise").hide();
    });

    $("#saveUser").on("click", function () {
        if (!isUserFormValid()) {
            showErrorModal("Hay campos con datos inválidos. Corrígelos antes de guardar.");
            return;
        }

        const data = {
            name: $("#name").val().trim(),
            lastname: $("#lastname").val().trim(),
            username: $("#username").val().trim(),
            email: $("#email").val().trim(),
            password: $("#password").val().trim(),
            level: $("[name='level']").val(),
            enterprises: selectedCompanies,
            module: 'users'
        };

        let payload, method;

        if (editingUserId) {
            payload = { update: { id: editingUserId, ...data } };
            method = 'PUT';
        } else {
            payload = { create: { ...data } };
            method = 'POST';
        }

        fetchAPI('user', method, payload)
        .then(() => {
            showSuccessModal("Se ha agregado un nuevo usuario");
            // location.reload();
        })
        .catch(() => {
            showErrorModal("Error al guardar el hotel.");
        });
    });

    $("#cancelUser").on("click", clearForm);

    if(window.userInfo.level === "master"){
        $(document).on("click", ".user-btn-edit", function () {

            const $card = $(this).closest("div[data-id]");
            let empresasRaw = $card.data("empresas");
            empresasRaw = empresasRaw.replace(/&quot;/g, '"');
            const empresas = JSON.parse(empresasRaw);

            const user = {
                id: $card.data("id"),
                name: $card.data("name"),
                lastname: $card.data("lastname"),
                username: $card.data("username"),
                email: $card.data("email"),
                level: $card.data("level"),
                empresas: empresas,
                ip: $card.data("ip")
            };

            fillForm(user);
        });
    }

    const userValidationRules = {
        "#name": regexTextArea,
        "#lastname": regexTextArea,
        "#username": regexTextArea,
        "#email": regexEmail,
        "#password": regexTextMetodoPayment
    };

    function attachUserValidation() {
        for (const selector in userValidationRules) {
            const $input = $(selector);
            const regex = userValidationRules[selector];

            let timer = null;

            $input.on("input", function () {
                const self = this;

                if (timer) clearTimeout(timer);

                timer = setTimeout(() => {
                    const val = $(self).val();
                    const [ban, msg] = validate_data(val, regex);

                    result_validate_data(self, selector.replace("#", ""), ban, msg);

                    const field = selector.replace("#", "");
                    window.userFormValid[field] = (ban === "correcto");

                }, 700);
            });
        }
    }

    function isUserFormValid() {
        return Object.values(window.userFormValid).every(v => v === true);
    }

    function validateSelectedCompanies() {
        let valid = false;

        if (selectedCompanies === "all") {
            valid = true;
        } else if (Array.isArray(selectedCompanies) && selectedCompanies.length > 0) {
            valid = true;
        }

        window.userFormValid.companies = valid;

        $("#companiesavailables").css("border", valid ? "" : "1px solid red");
    }

    attachUserValidation();
    clearForm();
});
</script>
