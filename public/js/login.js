$(document).ready(function() {
    $("#sendButton").on("click", function() { sendEvent(); });
});


function sendEvent() {
    function data(FormData) {
        var json = {};
        FormData.forEach((value, key) => {
            json[key] = value;
        });
        return json;
    }

    let formData = new FormData(document.getElementById("form-login"));
    let isValid = credentials_are_valid();
    if (!isValid) return;
    block_form(true);
    
    fetch(`${window.url_web}/login`, {
        method: "POST",
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        cache: "default",
        body: JSON.stringify(data(formData)),
    })
      .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 400) {
            $("#contenrMsj").html(`<div style="margin-bottom: 10px;"><p style="background: #ff3100; padding: 5px 15px; color: white; margin-bottom: 0;"><b>Error:</b> ${text.message}</p></div>`);
        } else if (status == 200) {
            $("#contenrMsj").html(`<div style="margin-bottom: 10px;"><p style="background: limegreen; padding: 5px 15px; color: white; margin-bottom: 0;">${text.message}</p></div>`);
            window.localStorage.setItem("__token", text.__token);
            window.location.href = text.redirect;
        }

        setTimeout(() => {
            $("[name='password']").val('');
            $("#contenrMsj").html('');
            block_form(false);
        }, 2000);
      })
      .catch((error) => {});
}


function credentials_are_valid() {
    function test(input) {
        let ban, msg;
        let campo = $(input).attr("name");
        let texto = $(input).val();

        switch (campo) {
            case "username":
                if (texto.length == 0) {
                    ban = "vacio";
                    msg = "Campo vacio.";
                } else if (!/^[A-Za-z0-9\s]*\(?[A-Za-z0-9\s]*\)?[A-Za-z0-9\s]*$/.test(texto)) {
                    ban = "invalido";
                    msg = "Datos no validos.";
                } else {
                    ban = "correcto";
                    msg = "";
                }
                break;
            case "password":
                if (texto.length == 0) {
                    ban = "vacio";
                    msg = "Campo vacio.";
                } else if (!/^[A-Za-z0-9._-]+$/.test(texto)) {
                    ban = "invalido";
                    msg = "Datos no validos.";
                } else {
                    ban = "correcto";
                    msg = "";
                }
                break;
        }

        if (ban == "invalido") $(input).css("box-shadow", "0px 0px 8px rgba(255, 0, 0, 0.6)").fadeIn("slow");
        if (ban == "vacio") $(input).css("box-shadow", "0px 0px 8px rgba(255, 0, 0, 0.6)").fadeIn("slow");

        setTimeout(() => {
            $(input).css("box-shadow", "none");
        }, 2000);
    
        if (ban == "correcto") {
            return true;
        } else {
            return false;
        }
    }

    let booleanArray = [];
    $("#form-login :input").each(function() {
        let boolean = test(this);
        booleanArray.push(boolean);
    });

    return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
}


function block_form(value = false) {
    $("[name='username']").prop("disabled", value);
    $("[name='password']").prop("disabled", value);
    
    $("#sendButton").text(value ? "Connecting..." : "Entrar");
    $("#sendButton").prop("disabled", value);
    $("#sendButton").css("cursor", value ? "not-allowed" : "default");
}