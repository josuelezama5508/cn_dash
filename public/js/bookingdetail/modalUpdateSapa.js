// modalUpdateSapa.js
let sapaEditData = "";
window.handleUpdateSapa = async function (idSapa) {
    try {
        const data = await search_id_details_family(idSapa);
        console.log("RESPUESTA");
        console.log(data);
      // Si es redondoI o redondoV llegan 2 objetos, si es sencillo solo 1
      const ida = data.find(d => d.type_transportation === 'redondoI' || d.type_transportation === 'sencillo');
      const vuelta = data.find(d => d.type_transportation === 'redondoV');
        
      // Llenar los campos comunes (nombre, pax, fecha, etc.)
      const base = ida || vuelta;
      console.log("BASE");
      console.log(base);
      if (!base) return console.warn('No se encontró información de SAPA.');
        
      $("#cliente_nombre").val(base.cname);
      $("#fecha_traslado").val(base.datepicker);
      $("#pax_cantidad").val(base.pax);
      $("#hora").val(base.horario);
      $("#comentario").val(base.folio || '');
      $("#hotelSearch").val(ida?.start_point || base.start_point || '');
      $("#origen").val(ida?.start_point || '');
      $("#destino").val(ida?.end_point || '');
  
      // Si hay vuelta, mostrar campos y llenarlos
      if (vuelta) {
        $("#campos-vuelta").show();
        $("#hora_vuelta").val(vuelta.horario);
        $("#origen_vuelta").val(vuelta.start_point);
        $("#destino_vuelta").val(vuelta.end_point);
      } else {
        $("#campos-vuelta").hide();
        $("#hora_vuelta").val('');
        $("#origen_vuelta").val('');
        $("#destino_vuelta").val('');
      }
  
      // Guardar en memoria los datos crudos por si se editan antes de enviar
      sapaEditData = { ida, vuelta, base };
    } catch (err) {
      console.error('Error cargando SAPA:', err);
    }
  };
  
  window.prepareUpdatePayload = function () {
    if (!sapaEditData) return null;

    const { ida, vuelta } = sapaEditData;

    const dataArray = [];

    if (ida) {
        dataArray.push({
            id: ida.id_sapa,
            datepicker: $("#fecha_traslado").val(),
            idpago: ida.idpago,
            folio: $("#comentario").val(),
            status_sapa: ida.id_estatus_sapa,
            usuario: ida.usuario,
            type: ida.type,
            horario: $("#hora").val(),
            start_point: $("#origen").val(),
            end_point: $("#destino").val(),
            cname: $("#cliente_nombre").val(),
            type_transportation: ida.type_transportation,
            idsapa: ida.id_sapa,
            matricula: ida.matricula,
            chofer_id: ida.chofer_id
        });
    }

    if (vuelta) {
        dataArray.push({
            id: vuelta.id_sapa,
            datepicker: $("#fecha_traslado").val(),
            idpago: vuelta.idpago,
            folio: $("#comentario").val(),
            status_sapa: vuelta.id_estatus_sapa,
            usuario: vuelta.usuario,
            type: vuelta.type,
            horario: $("#hora_vuelta").val(),
            start_point: $("#origen_vuelta").val(),
            end_point: $("#destino_vuelta").val(),
            cname: $("#cliente_nombre").val(),
            type_transportation: vuelta.type_transportation,
            idsapa: vuelta.id_sapa,
            matricula: vuelta.matricula,
            chofer_id: vuelta.chofer_id
        });
    }

    // Un solo objeto como lo pediste
    return {
        id: ida?.id_sapa || vuelta?.id_sapa,
        data: dataArray
    };
};
  
  window.submitUpdateSapa = async function () {
    const payload = window.prepareUpdatePayload();
    if (!payload) return console.warn('No hay datos para enviar.');
  
    const res = await fetchAPI("showsapa", "PUT", {
    ...payload,
    action: "Actualización de estado Sapa"
    });
    
    const result = await res.json();
    console.log('Resultado PUT SAPA', result);
    if (result.status === 200) {
        location.reload();
    }
  };
  
  window.initModalUpdateSapa = async function (idSapa) {
    
    // Cargar datos iniciales
    await handleUpdateSapa(idSapa);
    
    const modalData = sapaEditData?.base || {};
    const $input = $("#hotelSearch");
    const $list = $("#hotelList");
    const $initialSugContainer = $("#initialSuggestionsContainer");
    console.log("SAPA EDIT DATA");
    console.log(sapaEditData);
    console.log("modalData DATA");
    console.log(modalData);
    let debounceTimer;
    const horarioSearch = modalData.horario;
    console.log("horarioSearch DATA");
    console.log(horarioSearch);
    // Input eventos
    $input.on("click", function () {
      $(this).trigger("input");
    });
  
    $list.css({
      position: 'absolute',
      zIndex: 9999,
      maxHeight: '150px',
      overflowY: 'auto',
      width: '-webkit-fill-available',
      display: 'none',
      boxShadow: '0 2px 6px rgba(0,0,0,0.2)',
      background: 'white'
    });
  
    $input.on("input", function () {
      clearTimeout(debounceTimer);
      const query = $(this).val().trim();
      $("#initialSuggestionsContainer").hide();
  
      debounceTimer = setTimeout(async () => {
        let hoteles = [];
        try {
          const res = await search_transportation_tours(query, horarioSearch);
          hoteles = Array.isArray(res) ? res : [];
        } catch (err) {
          console.error(err);
        }
        renderTransportation($input, $list, hoteles, true);
        if (hoteles.length) $list.show();
        else $list.hide();
      }, 300);
    });
  
    $(document).on("click", function (e) {
      if (!$(e.target).closest('#hotelSearch').length) {
        $list.hide();
      }
    });
    // Evento universal para hacer clic en una tarjeta de hotel
    $(document).on("click", ".transport-card", function () {
        const origen = $(this).data("start");
        const destino = $(this).data("end");
    
        $("#origen").val(origen || "");
        $("#destino_vuelta").val(destino || "");
    });
    
  };
  