const ReservationValidator = (() => {
    const focusFirst = (el) => { 
        if (el) {
            el.scrollIntoView({behavior: "smooth", block: "center"});
            el.focus({preventScroll: true});
        }
    };

    const validateNombre = ($el) => {
        const input = $el ? $($el) : $('input[placeholder="Nombre"]');
        const valid = input.val().trim() && input.val().trim() !== "0";
        console.log("Validando Nombre:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateCompany = ($el) => {
        const input = $el || $('#companySelect');
        const valid = input.val() && input.val() !== "0";
        console.log("Validando Empresa:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateProduct = ($el) => {
        const input = $el || $('#productSelect');
        const valid = input.val() && input.val() !== "0";
        console.log("Validando Producto:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateTourTypeSelect = ($el) => {
        const input = $el || $('#tourtype');
        const valid = input.val() !== "";
        console.log("Validando Tipo de Servicio (select):", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateTourTypeInput = ($el) => {
        const $inputs = $el || $('#productdetailspax input[data-type="tour"]');
        let totalTickets = 0;
        $inputs.each((_, input) => {
            totalTickets += parseInt($(input).val()) || 0;
        });
        const valid = totalTickets > 0;
        console.log("Validando Tickets tipo tour:", valid);
        $inputs.toggleClass("input-error", !valid);
        return valid;
    };

    // const validateOtherTickets = ($el) => {
    //     const $inputs = $el || $('#productdetailspax input[type="text"]').not('[data-type="tour"]');
    //     let hasTickets = false;

    //     $inputs.each((_, input) => {
    //         if (parseInt($(input).val()) > 0) hasTickets = true;
    //     });

    //     console.log("Validando otros tickets:", hasTickets);
    //     $inputs.toggleClass("input-error", !hasTickets);
    //     return hasTickets;
    // };

    const validateChannel = ($el) => {
        const input = $el || $('#channelSelect');
        const valid = !!input.val();
        console.log("Validando Canal:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateLanguage = ($el) => {
        const input = $el || $('#language');
        const valid = !!input.val();
        console.log("Validando Idioma:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateDate = ($el) => {
        const input = $el || $("#datepicker");
        const fechaSelected = input[0]?._flatpickr?.selectedDates?.length > 0;
        console.log("Validando Fecha:", fechaSelected);
        input.toggleClass("input-error", !fechaSelected);
        return fechaSelected;
    };

    const validateHorario = () => {
        const $horarioCard = $('#horariosDisponibles .horario-card.seleccionado');
        const valid = $horarioCard.length > 0;
        console.log("Validando Horario:", valid);
        $('#horariosDisponibles').toggleClass("input-error", !valid);
        return valid;
    };

    const validateTotal = ($el) => {
        const input = $el || $('#totalPaxPrice');
        const valid = input.val() && parseFloat(input.val()) > 0;
        console.log("Validando Total:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };

    const validateOptionalFields = ($el) => {
        const value = $el.val().trim();
        const valid = value !== "";
        console.log("Validando campo opcional:", valid);
        if (valid) $el.removeClass("input-error");
    };

    const validateAll = () => {
        console.log("🧪 Iniciando validación completa...");
        let isValid = true;
        isValid &= validateNombre();
        isValid &= validateCompany();
        isValid &= validateProduct();
        isValid &= validateTourTypeSelect();
        isValid &= validateTourTypeInput();
        // isValid &= validateOtherTickets();
        isValid &= validateChannel();
        isValid &= validateLanguage();
        isValid &= validateDate();
        isValid &= validateHorario();
        isValid &= validateTotal();
        console.log("✅ Validación completa:", !!isValid);
        return !!isValid;
    };

    return {
        validateNombre,
        validateCompany,
        validateProduct,
        validateTourTypeSelect,
        validateTourTypeInput,
        // validateOtherTickets,
        validateChannel,
        validateLanguage,
        validateDate,
        validateHorario,
        validateTotal,
        validateOptionalFields,
        validateAll
    };
})();
