const validationRulesInput = {
  usuario_nombre: (value) => {
    if (!value.trim()) return { valid: false, message: "El nombre no puede estar vacío" };
    return { valid: true };
  },

  usuario_telefono: (value) => {
    if (!value.trim()) return { valid: true }; // opcional
    if (!/^\d{10}$/.test(value)) {
      return { valid: false, message: "El teléfono debe tener exactamente 10 dígitos" };
    }
    return { valid: true };
  },

  usuario_email: (value) => {
    if (!value.trim()) return { valid: false, message: "El email no puede estar vacío" };
    const emailRegex = /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i;
    if (!emailRegex.test(value)) {
      return { valid: false, message: "El correo no es válido (ej: nombre@dominio.com)" };
    }
    return { valid: true };
  },
  cliente_nombre: (value) => {
    if (!value.trim()) return { valid: false, message: "El nombre del cliente es obligatorio" };
    return { valid: true };
  },

  fecha_traslado: (value) => {
    if (!value.trim()) return { valid: false, message: "La fecha es obligatoria" };
    return { valid: true };
  },

  pax_cantidad: (value) => {
    if (!value.trim()) return { valid: false, message: "La cantidad de pax es obligatoria" };
    const num = parseInt(value);
    if (isNaN(num) || num <= 0) return { valid: false, message: "Debe ser un número válido" };
    return { valid: true };
  },

  origen: (value) => {
    if (!value.trim()) return { valid: false, message: "El punto de partida es obligatorio" };
    return { valid: true };
  },

  destino: (value) => {
    if (!value.trim()) return { valid: false, message: "El destino es obligatorio" };
    return { valid: true };
  },

  hora: (value) => {
    if (!value.trim()) return { valid: false, message: "El horario es obligatorio" };
    const horaRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;
    if (!horaRegex.test(value)) {
      return { valid: false, message: "Formato de hora inválido (HH:MM)" };
    }
    return { valid: true };
  },

  comentario: (value) => {
    if (!regexTextArea.test(value)) {
      return { valid: false, message: "No se permiten caracteres especiales" };
    }
    return { valid: true };
  }
};

  
  // Función genérica de validación por ID
function validateFieldById(fieldId, value) {
    const validator = validationRulesInput[fieldId];
    return validator ? validator(value) : { valid: true }; // si no hay regla definida, pasa
}
function mostrarToastOnObject(mensaje, $input, tipo = "success") {
  // Crear toast
  const toast = $(`
    <div class="toast align-items-center text-white ${tipo==='success'?'bg-success':'bg-danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position:absolute; z-index:9999;">
      <div class="d-flex">
        <div class="toast-body">${mensaje}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `);

  // Agregar al body
  $("body").append(toast);

  // Calcular posición sobre el input
  const offset = $input.offset();
  toast.css({
    top: offset.top - toast.outerHeight() - 5, // arriba del input
    left: offset.left,
    minWidth: $input.outerWidth()
  });

  const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
  bsToast.show();

  // Remover al ocultarse
  toast.on('hidden.bs.toast', () => toast.remove());
}