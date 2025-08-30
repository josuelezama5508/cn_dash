$(document).on("change", ".ctrl-number :input", function () {
    var min = parseInt($(this).attr("min"));
    var max = parseInt($(this).attr("max"));
    var value = parseInt($(this).val());

    if (value < min) {
        $(this).val(min); // Corrige si es menor al mínimo
    } else if (value > max) {
        $(this).val(max); // Corrige si es mayor al máximo
    }
});

$(document).on("keydown", ".ctrl-number :input", function(event) {
    if (event.key === "0" && $(this).val() === "") {
        event.preventDefault(); // Bloquea el "0" como primer número
    }
});

$(document).on("blur", ".ctrl-number :input", function() {
    var min = parseInt($(this).attr("min"));
    var max = parseInt($(this).attr("max"));
    var value = parseInt($(this).val());

    if (value < min || isNaN(value)) {
        $(this).val(min); // Ajusta si está vacío o menor al mínimo
    } else if (value > max) {
        $(this).val(max); // Ajusta si supera el máximo
    }
});