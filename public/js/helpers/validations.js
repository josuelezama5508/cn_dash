
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
    const validateLastName = ($el) => {
        const input = $el ? $($el) : $('input[placeholder="Apellidos"]');
        const val = input.val().trim();
        const isClean = /^[^<>()'*/\\"]*$/g.test(val);
        const valid = val === "" || isClean;
        console.log("Validando Comentarios:", valid);
        input.toggleClass("input-error", !valid && val !== "");
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
    function toJQuery(el) {
        return (el instanceof jQuery) ? el : $(el);
      }
      
    const validateChannel = ($el) => {
        const input = ($el instanceof jQuery) ? $el : $($el || '#channelSelect');
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

    // const validateHorario = () => {
    //     const $horarioCard = $('#horariosDisponibles .horario-card.seleccionado');
    //     const valid = $horarioCard.length > 0;
    //     console.log("Validando Horario:", valid);
    //     $('#horariosDisponibles').toggleClass("input-error", !valid);
    //     return valid;
    // };
    const validateHorario = ($el) => {
        const input = $el || $('#selectHorario');
        const valid = !!input.val();
        console.log("Validando Horario:", valid);
        input.toggleClass("input-error", !valid);
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
    const validateEmail = ($el) => {
        const input = $el || $('input[placeholder="Correo Cliente"]');
        const val = input.val().trim();
        const valid = val === "" || /^[\w\.-]+@[\w\.-]+\.\w{2,}$/.test(val);
        console.log("Validando Correo:", valid);
        input.toggleClass("input-error", !valid && val !== "");
        return valid;
    };
    const validatePhone = ($el) => {
        const input = $el || $('input[placeholder="Telefono Cliente"]');
        const val = input.val().trim();
        const valid = val === "" || /^\d{10}$/.test(val);
        console.log("Validando Teléfono:", valid);
        input.toggleClass("input-error", !valid && val !== "");
        return valid;
    };
    const safeTextRegex = /^[a-zA-Z0-9\s]*$/;
    const validateComments = ($el) => {
        const input = $el || $('.comentario-opcional');
        const val = input.val().trim();
        const isClean = safeTextRegex.test(val);
        const valid = val === "" || isClean;
        console.log("Validando Comentarios:", valid);
        input.toggleClass("input-error", !valid && val !== "");
        return valid;
    };
    const validateCommentsVoucher = ($el) => {
        const input = $el || $("#voucherCode");
        const val = input.val().trim();
        const valid = val !== "" && safeTextRegex.test(val); // requerido + sin caracteres raros
        console.log("Validando Voucher:", valid);
        input.toggleClass("input-error", !valid);
        return valid;
    };
    const validateAll = (soloAddons) => {
        console.log("🧪 Iniciando validación completa...");
        console.log("🧪 VALOR DE SOLO ADDONS... " + soloAddons);

        let isValid = true;
    
        isValid &= validateNombre();
        isValid &= validateLastName();
        isValid &= validateCompany();
        isValid &= validateProduct();
        isValid &= validateTourTypeSelect();
        
        isValid &= validateChannel();
        isValid &= validateLanguage();
        isValid &= validateDate();
        if(!soloAddons){
            isValid &= validateTourTypeInput();
            isValid &= validateHorario();
        }
        isValid &= validateTotal();
    
        // 🔹 Validaciones opcionales pero obligatorias si tienen valor
        isValid &= validateEmail();
        isValid &= validatePhone();
        isValid &= validateComments();
        console.log("✅ Validación completa:", !!isValid);
        return !!isValid;
    };
    const validateAllVoucher = (soloAddons) => {
        console.log("🧪 Iniciando validación completa...");
        console.log("🧪 VALOR DE SOLO ADDONS... " + soloAddons);
        let isValid = true;
    
        isValid &= validateNombre();
        isValid &= validateLastName();
        isValid &= validateCompany();
        isValid &= validateProduct();
        isValid &= validateTourTypeSelect();
        isValid &= validateChannel();
        isValid &= validateLanguage();
        isValid &= validateDate();
        if(!soloAddons){
            isValid &= validateHorario(); 
            isValid &= validateTourTypeInput();
        }
        isValid &= validateTotal();
    
        // 🔹 Validaciones opcionales pero obligatorias si tienen valor
        isValid &= validateEmail();
        isValid &= validatePhone();
        isValid &= validateComments();
        isValid &= validateCommentsVoucher();
        console.log("✅ Validación completa:", !!isValid);
        return !!isValid;
    };

    return {
        validateNombre,
        validateLastName,
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
        validateEmail,         // 👈 Agrega esto
        validatePhone,         // 👈 Agrega esto
        validateComments,      // 👈 Y esto
        validateCommentsVoucher,
        validateAll,
        validateAllVoucher,
    };

})();
