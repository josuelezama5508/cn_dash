// Reglas dinámicas de validación por ID de input
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
    }
    // 👆 hotel y cuarto no tienen reglas
  };
  
  // Función genérica de validación por ID
function validateFieldById(fieldId, value) {
    const validator = validationRulesInput[fieldId];
    return validator ? validator(value) : { valid: true }; // si no hay regla definida, pasa
}
  