// modalSearchT.js
window.initModalSearchT = function(modalData = {}) {
    if (!$("#modalSearchT").length) {
        $("body").append(`
            <div id="overlayT" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; z-index: 1500;"></div>
            <div id="modalSearchT" style="
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 1200px;
                height: auto;
                background: #f9f9f9;
                border-radius: 12px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.35);
                z-index: 1501;
                padding: 20px;
                display: flex;
                flex-direction: column;
                max-height: 500px;
            ">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                    <h4 style="margin:0; font-weight:600; color:#333;">Buscar registros</h4>
                    <button id="closeModalT" class="btn btn-sm btn-danger" style="font-weight:600; border-radius:6px;">X</button>
                </div>
                <input type="search" id="searchT" placeholder="Buscar..." class="form-control mb-3" style="border-radius:8px; border:1px solid #ccc; padding:10px;">
                <div id="tableContainerT" style="overflow-y:auto; flex: 1; border-top:1px solid #ddd; padding-top:10px;"></div>
            </div>
        `);
    }

    const $modal = $("#modalSearchT");
    const $overlay = $("#overlayT");
    const $input = $("#searchT");
    const $container = $("#tableContainerT");

    function openModal() { $overlay.show(); $modal.fadeIn(); }
    function closeModal() { $modal.fadeOut(); $overlay.hide(); }
    $("#closeModalT").on("click", closeModal);
    $overlay.on("click", closeModal);

    function renderSearchT(items) {
        $container.empty();
        if (!Array.isArray(items) || items.length === 0) {
            $container.html('<div class="text-center text-muted" style="padding:20px; font-style:italic;">No se encontraron registros</div>');
            return;
        }

        const $table = $(`
            <table class="table mb-0" style="border-collapse: separate; border-spacing: 0 5px; width:100%;">
                <thead>
                    <tr style="background:#e3f2fd;">
                        <th style="border:none; padding:8px;">Hotel</th>
                        <th style="border:none; padding:8px;">Ubicación</th>
                        <th style="border:none; padding:8px;">Direccion</th>
                        <th style="border:none; padding:8px;">Horario 1</th>
                        <th style="border:none; padding:8px;">Horario 2</th>
                        <th style="border:none; padding:8px;">Horario 3</th>
                        <th style="border:none; padding:8px;">Horario 4</th>
                        <th style="border:none; padding:8px;">Horario 5</th>
                        <th style="border:none; padding:8px;">Nocturno</th>
                        <th style="border:none; padding:8px;">Horario 7</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        `);

        items.forEach(tr => {
            const tours = ['tour1','tour2','tour3','tour4','tour5','nocturno','tour7'].map(t => {
                if (tr[t]) {
                    let nombre = t === 'nocturno' ? 'Nocturno' : t.replace('tour','Tour ');
                    return `<span>
                                ${tr[t]}
                            </span>`;
                }
                return '<span style="color:#999;">-</span>';
            });

            const sugerencia = ['WYNDHAM ALTRA','PRESIDENTE INTERCONT','XTEND SUITES'].includes(tr.hotel)
                ? '<span class="badge bg-success" style="font-size:0.85em;">Sugerencia</span>'
                : '';

            const $row = $(`
                <tr style="background:#fff; border-bottom:1px solid #eee;">
                    <td style="padding:8px;">${tr.hotel}</td>
                    <td style="padding:8px;">${tr.ubicacion}</td>
                    <td style="padding:8px;">${tr.direccion}</td>
                    <td style="padding:4px;">${tours[0]}</td>
                    <td style="padding:4px;">${tours[1]}</td>
                    <td style="padding:4px;">${tours[2]}</td>
                    <td style="padding:4px;">${tours[3]}</td>
                    <td style="padding:4px;">${tours[4]}</td>
                    <td style="padding:4px;">${tours[5]}</td>
                    <td style="padding:4px;">${tours[6]}</td>
                </tr>
            `);

            $table.find('tbody').append($row);
        });

        $container.append($table);


        $container.show();
    }

    let debounceTimer;
    $input.on("input", function() {
        const query = $(this).val().trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            let hoteles = [];
            try {
                const res = await search_transportation_home(query, modalData);
                hoteles = Array.isArray(res) ? res : [];
            } catch (err) { console.error(err); }
            renderSearchT(hoteles);
        }, 200);
    });

    // $(document).on("click", function(e) {
    //     if (!$(e.target).closest('#searchT').length) $container.hide();
    // });

    openModal();
    $input.val('');
    $input.focus();

    // Carga inicial
    search_transportation_home("").then(hoteles => renderSearchT(hoteles));
}
