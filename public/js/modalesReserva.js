// Función para abrir cualquier modal dinámico
// Abrir el modal y cargar contenido dinámico
async function openModal(url, title = "") {
    try {
        const res = await fetch(url);
        const html = await res.text();
        console.log("MODAL DATA: " + modalData);

        // Carga el contenido
        document.getElementById("modalGenericContent").innerHTML = html;
        document.getElementById("modalGenericTitle").innerText = title;
        
        
        // ⚠️ Asignar bookingID si existe
        if (modalData?.id && document.getElementById("idpago")) {
            document.getElementById("idpago").value = modalData.id;
        }
        // Inicializa y muestra el modal
        const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
        modal.show();
        window.currentModal = modal;
        if (url.includes('form_cancelar')) {
            const total = Number(modalData?.total) || 0;

            const nombreCliente = `${modalData.cliente_name ?? '-'} ${modalData.cliente_lastname ?? ''}`.trim() || '-';;
            const emailCliente = modalData?.email ?? '-';
            
            document.getElementById('total_reserva').innerText = `$${total.toFixed(2)} USD`;
            document.getElementById('nombre_cliente').innerText = nombreCliente;
            document.getElementById('email_cliente').innerText = emailCliente;
            let monedaActual = 'USD'; // Valor inicial
            let factorConversion = 20; // USD → MXN
            
            const actualizarMonedaVisual = () => {
                const isMXN = monedaActual === 'MXN';
                const currencySymbol = '$';
                const currencyLabel = isMXN ? 'MXN' : 'USD';
                document.getElementById('descuento_dinero').value = '';
                document.getElementById('currency_symbol_dinero').innerText = currencySymbol;
                document.getElementById('currency_label').innerText = currencyLabel;
                document.getElementById('total_reserva').innerText = `${currencySymbol}${getTotal().toFixed(2)} ${currencyLabel}`;
                actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val()));
            };
            
            const getTotal = () => {
                return monedaActual === 'MXN' ? total * factorConversion : total;
            };
            // Función para actualizar montos
            function actualizarMontos(baseTotal, porcentajeReembolso) {
                const descuentoDinero = parseFloat($('#descuento_dinero').val()) || 0;
            
                const totalDescuento = descuentoDinero;
            
                const totalConDescuento = baseTotal - totalDescuento;
                const montoReembolso = totalConDescuento * (porcentajeReembolso / 100);
                const penalizacion = baseTotal - montoReembolso;
            
                const label = monedaActual;
            
                document.getElementById('descuento_aplicado').innerText = `$${totalDescuento.toFixed(2)} ${label}`;
                document.getElementById('monto_reembolso').innerText = `$${montoReembolso.toFixed(2)} ${label}`;
                document.getElementById('penalizacion_cancelacion').innerText = `$${penalizacion.toFixed(2)} ${label}`;
            }
            
            $('#descuento_dinero').on('input', () => {
                actualizarMontos(getTotal(), Number($('#porcentaje_reembolso').val()));
            });
            
            document.getElementById('currency_label')?.addEventListener('click', () => {
                monedaActual = monedaActual === 'USD' ? 'MXN' : 'USD';
                actualizarMonedaVisual();
            });
            
            actualizarMonedaVisual();                        
            const $select = $('#motivo_cancelacion');
            const $porcentajeInput = $('#porcentaje_reembolso');



            
            const $categoriaSelect = $('#categoria_cancelacion');

            const loadCancellationCategories = async () => {
                try {
                    const response = await fetchAPI('cancellation?cancellationDispoCategory=', 'GET');
                    const json = await response.json();
    
                    if (response.ok && json.data?.length) {
                    $categoriaSelect.empty();
                    $categoriaSelect.append('<option value="" disabled selected>Selecciona una categoría</option>');
                    json.data
                        .filter(cat => cat.status === 1)
                        .forEach(cat => {
                        $categoriaSelect.append(`
                            <option value="${cat.id}"
                                    data-name-es="${cat.name_es}"
                                    data-name-en="${cat.name_en}">
                            ${cat.name_es}
                            </option>
                        `);
                        });
                    } else {
                    console.warn('No se encontraron categorías de cancelación.');
                    }
                } catch (error) {
                    console.error('Error al cargar categorías de cancelación:', error);
                }
                };
    
                await loadCancellationCategories();
            // Cargar motivos de cancelación
            const loadCancellationTypes = async () => {
            try {
                const response = await fetchAPI('cancellation?cancellationDispo=', 'GET');
                const data = await response.json();
            
                if (response.ok && data.data?.length) {
                $select.empty();
                $select.append('<option value="" disabled selected>Selecciona un motivo</option>');
            
                const activeTypes = data.data
                    .filter(item => item.status === 1)
                    .sort((a, b) => a.sort_order - b.sort_order);
            
                activeTypes.forEach(type => {
                    $select.append(
                    `<option value="${type.id}" data-refund="${type.refund_percentage}">${type.name_es}</option>`
                    );
                });
            
                if (activeTypes.length > 0) {
                    $select.val(activeTypes[0].id);
                    $porcentajeInput.val(activeTypes[0].refund_percentage);
            
                    // Inicialmente, el input porcentaje solo editable si es 'Otro' (ID 9)
                    $porcentajeInput.prop('disabled', activeTypes[0].id !== 9);
            
                    actualizarMontos(total, activeTypes[0].refund_percentage);
                }
                } else {
                console.warn('No se encontraron tipos de cancelación.');
                }
            } catch (error) {
                console.error('Error al cargar tipos de cancelación:', error);
            }
            };
            await loadCancellationTypes();
            // Evento cambio motivo cancelación
            $select.on('change', function() {
            const selectedId = parseInt($(this).val());
            const refund = $(this).find(':selected').data('refund') ?? 0;
            
            $porcentajeInput.val(refund);
            
            // Si el motivo es ID 9 (Otro), habilitar input para editar manualmente
            if (selectedId === 9) {
                $porcentajeInput.prop('disabled', false);
            } else {
                $porcentajeInput.prop('disabled', true);
            }
            
            actualizarMontos(total, refund);
            });
            
            // Evento para editar porcentaje manualmente solo si está habilitado
            $porcentajeInput.on('input', function() {
            if ($(this).prop('disabled')) return; // no hacer nada si está deshabilitado
            
            let val = parseFloat($(this).val());
            if (isNaN(val) || val < 0) val = 0;
            else if (val > 100) val = 100;
            
            $(this).val(val);
            actualizarMontos(total, val);
            });
          }
        // Elementos importantes
        const radios = document.querySelectorAll('input[name="notificacion_tipo"]');
        const pickupFields = document.getElementById('pickup_fields');
        const solicitarIdToggle = document.getElementById('solicitar_id');
        // --- PICKUP ---
        if (pickupFields) pickupFields.classList.add('d-none'); // Oculta por defecto

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                // Solo Pick up
                if (document.getElementById('pickup')?.checked) {
                    pickupFields.classList.remove('d-none');
                } else if (pickupFields) {
                    pickupFields.classList.add('d-none');
                }

                // Voucher o Mal clima ocultan toggle
                if (document.getElementById('voucher')?.checked || document.getElementById('clima')?.checked) {
                    if (solicitarIdToggle) solicitarIdToggle.closest('.form-check').style.display = 'none';
                } else if (solicitarIdToggle) {
                    solicitarIdToggle.closest('.form-check').style.display = 'flex';
                }
            });
        });

        // Estado inicial al abrir modal
        if (document.getElementById('pickup')?.checked) pickupFields.classList.remove('d-none');
        if ((document.getElementById('voucher')?.checked || document.getElementById('clima')?.checked) && solicitarIdToggle) {
            solicitarIdToggle.closest('.form-check').style.display = 'none';
        }
        const btnGuardar = document.querySelector("#modalGeneric .btn-primary");

        if (url.includes('form_mail')) {
            btnGuardar.onclick = handleMail;
        } else if (url.includes('form_sapa')) {
            btnGuardar.onclick = confirmSapa;
        }else if(url.includes('form_cancelar')){
            btnGuardar.onclick = handleMailCancel;
        }else {
            btnGuardar.onclick = () => {
                alert("Acción no implementada para este formulario.");
            };
        }
        
    } catch (err) {
        console.error("Error al cargar el contenido:", err);
        alert("No se pudo cargar el formulario.");
    }
}
  // Función de envío del formulario (ejemplo para Sapa)
async function confirmSapa() {
    const data = {
      create: {
        tipo: document.querySelector('input[name="transporte_tipo"]:checked')?.value,
        cliente_name: document.getElementById("cliente_nombre")?.value,
        datepicker: document.getElementById("fecha_traslado")?.value,
        personas: document.getElementById("personas")?.value,
        origen: document.getElementById("origen")?.value,
        destino: document.getElementById("destino")?.value,
        horario: document.getElementById("hora")?.value,
        nota: document.getElementById("comentario")?.value,
      }
    };
  
    try {
      const res = await fetch(`${window.url_web}/api/control`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
  
      const result = await res.json();
      if (res.ok) {
        alert("Reserva creada exitosamente");
        closeModal();
      } else {
        alert("Error: " + result.message);
      }
    } catch (err) {
      console.error(err);
      alert("Error al enviar la reserva");
    }
  }

// Función para cerrar el modal
function closeModal() {
    if (window.currentModal) {
        // Oculta el modal
        window.currentModal.hide();

        // Elimina cualquier backdrop que quede
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();

        // Limpia la referencia para que no haya problemas
        window.currentModal = null;
    }
}
  // Función para abrir modal de reagendar con form
// Abrir modal de reagendar con datos
async function openReagendarModal(modalData) {
    const html = `
    <form id="form_update_reagendar">
        <input type="hidden" id="reserva_id" value="${modalData.id}">
        <div class="mb-3">
            <label for="datepicker" class="form-label fw-bold">Nueva Fecha</label>
            <input type="text" id="datepicker" class="form-control" required value="${modalData.datepicker}">
        </div>

        <div class="mb-3">
            <label for="nuevo_horario" class="form-label fw-bold">Nuevo Horario</label>
            <select id="nuevo_horario" class="form-select" required>
                <option value="${modalData.horario}" selected>${modalData.horario}</option>
            </select>
        </div>
    </form>
    `;

    document.getElementById("modalGenericContent").innerHTML = html;
    document.getElementById("modalGenericTitle").innerText = "Reagendar Reserva";

    // Cambiar acción del botón de guardar
    const btnGuardar = document.querySelector("#modalGeneric .btn-primary");
    btnGuardar.onclick = confirmReagendar;

    // Inicializar y mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalGeneric'));
    modal.show();
    window.currentModal = modal;

    // Inicializa Flatpickr y renderiza horarios
    setupCalendario(modalData.code_company);
}

// Flatpickr + render de horarios
function setupCalendario(companycode) {
    flatpickr("#datepicker", {
        inline: true,
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: document.getElementById("datepicker").value,
        onChange: async function(selectedDates, dateStr) {
            // Actualiza la fecha en el resumen
            // Llamada a la API para obtener horarios según la fecha seleccionada
            try {
                const response = await fetch(`${window.url_web}/api/control?getByDispo[empresa]=${companycode}&getByDispo[fecha]=${dateStr}`);
                const result = await response.json();

                const select = document.getElementById("nuevo_horario");
                select.innerHTML = "";

                if (response.ok && Array.isArray(result?.data) && result.data.length > 0) {
                    const horarios = result.data.filter(h => h.disponibilidad > 0);
                    horarios.forEach(h => {
                        const option = document.createElement("option");
                        option.value = h.hora;
                        option.textContent = `${h.hora} (${h.disponibilidad} disponibles)`;
                        select.appendChild(option);
                    });
                } else {
                    const option = document.createElement("option");
                    option.value = "";
                    option.textContent = "Sin horarios disponibles";
                    select.appendChild(option);
                }
            } catch (error) {
                console.error("Error al obtener disponibilidad:", error);
            }
        }
    });
}
//FUNCIONA
async function confirmReagendar() {
    const form = document.getElementById('form_update_reagendar');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const nuevaFecha = document.getElementById('datepicker').value;
    const nuevoHorario = document.getElementById('nuevo_horario').value;
    const reservaId = document.getElementById('reserva_id').value;
    // Crear los datos con la estructura correcta
    const data = {
        reagendar: {  // Usamos 'reagendar' como la acción esperada por tu API
            idpago: parseInt(reservaId),
            datepicker: nuevaFecha,
            horario: nuevoHorario,
            tipo: 'reagendacion'
        }
    };
    try {
        // Usamos fetchAPI para hacer el PUT con los datos
        const response = await fetchAPI('control', 'PUT', data);  // Aquí 'control' es el endpoint
        // Verifica el código de estado y la respuesta
        if (response.ok) {
            closeModal();

            // Recargar la página después de una respuesta exitosa
            location.reload();
        } else {
            const result = await response.json();
            alert("Error: " + result.message);
        }
    } catch (error) {
        console.error("Error en la solicitud:", error);
        alert("Error al reagendar la reserva");
    }
}
// ========================
// RESERVAS VINCULADAS
// ========================

// Renderiza tabla simple de reservas vinculadas
function renderizarReservasVinculadas(reservas) {
    if (!Array.isArray(reservas) || reservas.length === 0) {
        return `<p class="text-center">No hay reservas vinculadas.</p>`;
    }

    const rows = reservas.map(r => `
        <tr>
            <td>${r?.datepicker ?? '-'}</td>
            <td>${r?.horario ?? '-'}</td>
            <td>${(r?.cliente_name ?? '-') + ' ' + (r?.cliente_lastname ?? '-')}</td>
            <td>${r?.actividad ?? '-'}</td>
            <td>${r?.nog ?? '-'}</td>
            <td>${r?.total != null ? r.total : 0}</td>
            <td>${r?.statusname ?? '-'}</td>
            <td>
                <button class="btn btn-sm btn-primary ver-detalle" data-nog="${r?.nog ?? ''}">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');

    return `
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Cliente</th>
                        <th>Actividad</th>
                        <th>NOG</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}
function showNotification(message, type = 'danger') {
    // type: 'success', 'danger', 'warning', etc.
    const container = document.getElementById('modalNotificationContainer');
    if (!container) return;

    const notif = document.createElement('div');
    notif.className = `alert alert-${type} alert-dismissible fade show mt-2`;
    notif.role = 'alert';
    notif.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    container.appendChild(notif);
    // Auto cerrar después de 4s
    setTimeout(() => notif.remove(), 4000);
}

async function openModalReservasVinculadas(nog) {
    try {
        const res = await fetchAPI(`control?vinculados=${encodeURIComponent(nog)}`, "GET");
        const json = await res.json();
        const reservas = (json?.data || []).filter(r => r?.nog && r.nog !== nog);

        document.getElementById("reservasVinculadasContent").innerHTML = renderizarReservasVinculadas(reservas);

        const modal = new bootstrap.Modal(document.getElementById('modalReservasVinculadas'));
        modal.show();
        window.currentReservasModal = modal;

        document.querySelectorAll("#reservasVinculadasContent .ver-detalle").forEach(btn => {
            btn.addEventListener("click", () => {
                const nog = btn.dataset?.nog;
                if (nog) {
                    window.location.href = `${window.url_web}/detalles-reserva/view?nog=${nog}`;
                } else {
                    showNotification("NOG inválido para esta reserva.", 'warning');
                }
            });
        });

    } catch (err) {
        console.error("Error al cargar reservas vinculadas:", err);
        showNotification("No se pudieron cargar las reservas vinculadas.", 'danger');
    }
}


// Función para cerrar modal
function closeModalReservas() {
    if (window.currentReservasModal) {
        window.currentReservasModal.hide();
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
        window.currentReservasModal = null;
    }
}



// Evento botón reagendar
document.querySelectorAll('.btn-primary').forEach(btn => {
    if (btn.innerText.includes("Reagendar Reserva")) {
        btn.addEventListener('click', () => openReagendarModal());
    }
});
// Ejemplo: abrir modal de Sapa
document.querySelectorAll('.btn-info').forEach(btn => {
    btn.addEventListener('click', () => {
        openModal(`${window.url_web}/detalles-reserva/form_sapa`);
    });
});
document.querySelectorAll('.btn-success').forEach(btn => {
    btn.addEventListener('click', () => {
        openModal(`${window.url_web}/detalles-reserva/form_mail`);
    });
});
document.querySelectorAll('.btn-primary').forEach(btn => {
    if (btn.innerText.includes("Reagendar Reserva")) {
        btn.addEventListener('click', () => openReagendarModal(modalData));
    }
});
document.querySelectorAll('.btn-dark').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.addEventListener('click', openModalReservasVinculadas(modalData['nog']));
    });
});
document.querySelectorAll('.btn-warning').forEach(btn => {
    btn.addEventListener('click', () => {
        openModal(`${window.url_web}/detalles-reserva/form_mail`);
    });
});
document.querySelectorAll('.btn-danger').forEach(btn => {
    btn.addEventListener('click', () => {
        openModal(`${window.url_web}/detalles-reserva/form_cancelar`);
    });
});
//FUNCIONA
async function handleMail() {
    const tipoId = document.querySelector('input[name="notificacion_tipo"]:checked')?.id;
    if (!tipoId) {
        alert("Selecciona un tipo de notificación.");
        return;
    }

    const idioma = document.querySelector('input[name="idioma"]:checked')?.value || 'es';
    const solicitarId = document.getElementById('solicitar_id')?.checked || false;
    const destinatario = document.getElementById('destinatario')?.value.trim();
    const correo = document.getElementById('correo_destino')?.value.trim();
    const comentario = document.getElementById('comentario_notif')?.value.trim();
    const idpago = document.getElementById('idpago')?.value;

    if (!destinatario || !correo) {
        alert("Por favor completa el nombre del destinatario y correo.");
        return;
    }

    // Base común
    const baseData = {
        idpago: parseInt(idpago),
        idioma,
        solicitar_id: solicitarId,
        destinatario,
        correo,
        comentario
    };

    let data = {};
    let funcion = ''; // 🔑 Variable dinámica para la clave en fetchAPI

    switch (tipoId) {
        case 'confirmacion':
            data = {
                ...baseData,
                tipo: 'confirmacion',
                procesado: 1
            };
            funcion = 'procesado';
            break;
        case 'voucher':
            data = {
                ...baseData,
                tipo: 'voucher'
            };
            funcion = 'voucher';
            break;
        case 'recibo':
            data = {
                ...baseData,
                tipo: 'recibo'
            };
            funcion = 'recibo';
            break;
        case 'pickup':
            const pickupHorario = document.getElementById('pickup_horario')?.value;
            const pickupLugar = document.getElementById('pickup_lugar')?.value.trim();

            if (!pickupHorario || !pickupLugar) {
                alert("Por favor completa el horario y lugar de pick up.");
                return;
            }

            data = {
                ...baseData,
                tipo: 'pickup',
                pickup_horario: pickupHorario,
                pickup_lugar: pickupLugar
            };
            funcion = 'pickup';
            break;
        default:
            alert("Tipo de notificación no válido.");
            return;
    }
    data.module = 'DetalleReservas';
    console.log("Datos enviados:", data);
    console.log("Clave de función:", funcion);

    try {
        const response = await fetchAPI('control', 'PUT', {
            [funcion]: data  // 🧠 clave dinámica
        });

        if (response.ok) {
            const result = await response.json();

            console.log("✅ Mensaje:", result.message);
            console.log("📦 Control actualizado:", result.data);
            closeModal();
            location.reload();
        } else {
            const result = await response.json();
            console.error("❌ Error:", result);
            alert("Error al procesar la notificación: " + (result.message || "Error desconocido"));
        }
    } catch (error) {
        console.error("🚫 Error en la solicitud:", error);
        alert("Error en la conexión. Intenta de nuevo más tarde.");
    }
}
async function handleMailCancel() {
    // El motivo de cancelación seleccionado
    const motivoId = document.getElementById('motivo_cancelacion')?.value;
    if (!motivoId) {
        alert("Selecciona un motivo de cancelación.");
        return;
    }

    // Porcentaje de reembolso (puede ser editable o no según motivo)
    const porcentajeReembolso = parseFloat(document.getElementById('porcentaje_reembolso')?.value) || 0;

    // Descuento porcentaje o dinero (solo uno se usa)
    const descuentoPorcentaje = 0;

    const descuentoDinero = parseFloat(document.getElementById('descuento_dinero')?.value) || 0;

    // Comentario de cancelación
    const comentario = document.getElementById('comentario_cancelacion')?.value.trim() || '';

    // ID pago y datos del cliente
    const idpago = modalData.id;
    const nombreCliente = document.getElementById('nombre_cliente')?.innerText || '';
    const correoCliente = document.getElementById('email_cliente')?.innerText || '';
    const categoriaId = parseInt(document.getElementById('categoria_cancelacion')?.value) || null;

    if (!idpago) {
        alert("ID de pago no encontrado.");
        return;
    }
    // Obtener total sin símbolo y sin moneda, convertir a número
    let totalText = document.getElementById('total_reserva')?.innerText || "$0.00";
    // Ejemplo: "$123.45 USD" → queremos solo número 123.45
    // Primero quitamos el signo $ y luego la moneda (asumiendo formato "$123.45 USD")
    totalText = totalText.replace('$', '').replace(/[^\d.,]/g, '').trim();
    const total = parseFloat(totalText.replace(',', '.')) || 0;

    // Obtener moneda actual (USD o MXN)
    const moneda = document.getElementById('currency_label')?.innerText || 'USD';
    // Construir objeto para enviar
    const cancelData = {
        idpago: parseInt(idpago),
        motivo_cancelacion_id: parseInt(motivoId),
        porcentaje_reembolso: porcentajeReembolso,
        descuento_porcentaje: descuentoPorcentaje,
        descuento_dinero: descuentoDinero,
        comentario,
        nombre_cliente: nombreCliente,
        correo_cliente: correoCliente,
        total,
        moneda,
        status: 2,
        categoriaId,
        tipo: 'cancelar',
        module: 'DetalleReservas',
        procesado: null
    };
    console.log(cancelData);
    try {
        const response = await fetchAPI('control', 'PUT', {
            cancelar: cancelData
        });

        if (response.ok) {
            const result = await response.json();
            console.log("✅ Mensaje:", result.message);
            console.log("📦 Control actualizado:", result.data);
            closeModal();
            location.reload();
        } else {
            const result = await response.json();
            console.error("❌ Error:", result);
            alert("Error al procesar la cancelación: " + (result.message || "Error desconocido"));
        }
    } catch (error) {
        console.error("🚫 Error en la solicitud:", error);
        alert("Error en la conexión. Intenta de nuevo más tarde.");
    }
}




