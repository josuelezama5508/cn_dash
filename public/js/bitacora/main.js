$(function () {

    const $tbody  = $('#SRBuscador');
    const $detail = $('#divSisReport');
    let cache = {};

    // =====================
    // SEARCH
    // =====================
    $('input[name="search"]').on('input', debounce(function () {
        loadReports($(this).val());
    }, 300));

    // =====================
    // CLICK EN REGISTRO
    // =====================
    $tbody.on('click', '.module-group', function () {
        const module = $(this).data('module');
        const rowId  = $(this).data('row');

        if (cache[module] && cache[module][rowId]) {
            renderInlineDetail($(this), cache[module][rowId], module);

        }
    });
    
    // INIT
    loadReports('');

    // =====================
    // LOAD
    // =====================
    async function loadReports(search) {
        $tbody.html('<tr><td><em>Cargando...</em></td></tr>');
        $detail.html('');

        const res = await search_reports(search);

        if (!res) {
            cache = {};
            $tbody.html('<tr><td><em>No hay resultados</em></td></tr>');
            return;
        }

        cache = res;
        renderList(cache);
        $detail.html('<em>Selecciona un registro</em>');
    }

    // =====================
    // LISTA AGRUPADA
    // =====================
    function renderList(data) {
        const html = [];

        Object.entries(data).forEach(([module, rows]) => {

            // --- TÍTULO DEL GRUPO ---
            html.push(`
                <tr class="module-title-row">
                    <td>
                        <strong class="module-title">${module}</strong>
                    </td>
                </tr>
            `);

            Object.entries(rows).forEach(([rowId, group]) => {

                if (!group.items || !group.items.length) return;

                const last = group.items[0];

                html.push(`
                    <tr class="module-group"
                        data-module="${module}"
                        data-row="${rowId}">
                        <td class="module-item">
                            <div class="row-id">ID: ${rowId}</div>
                            <small>${last.action} · ${last.timestamp}</small>
                        </td>
                    </tr>
                `);
            });
        });

        $tbody.html(
            html.length
                ? html.join('')
                : '<tr><td><em>No hay resultados</em></td></tr>'
        );
    }

    // =====================
    // DETALLE
    // =====================
    function renderInlineDetail($row, group, module) {
        if ($row.next().hasClass('inline-detail-row')) {
            $row.next().remove();
            return;
        }
        
        // 🔥 cerrar cualquier otro detalle abierto
        $('.inline-detail-row').remove();
    
        if (!group || !group.items || !group.items.length) return;
    
        const html = [];
    
        html.push(`<h6 class="mb-2">${module} · Row ${group.row_id}</h6>`);
    
        group.items.forEach(item => {
    
            html.push(`
                <div class="history-item ${item.action}">
                    <div class="history-meta">
                        #${item.id} · ${item.action} · ${item.timestamp}
                    </div>
            `);
    
            if (module === 'DetalleReservas' && item.old_data) {
                html.push(renderDetalleReservasHistory(item));
            } else {
                html.push(`
                    <div class="history-details">
                        ${item.details || '<em>Sin detalles</em>'}
                    </div>
                `);
            }
    
            html.push(`</div>`);
        });
    
        const detailRow = `
            <tr class="inline-detail-row">
                <td colspan="1">
                    <div class="inline-detail p-3 bg-light border rounded">
                        ${html.join('')}
                    </div>
                </td>
            </tr>
        `;
    
        $row.after(detailRow);
    }
    

    // =====================
    // DETALLE RESERVAS (HISTORIAL)
    function renderDetalleReservasHistory(item) {

        const oldArr = normalizeOldData(item.old_data);
        const newObj = safeJSON(item.new_data, null);
    
        if (!oldArr.length || !newObj?.control) {
            return `<em>Historial inválido</em>`;
        }
    
        const html = [];
    
        // cada item es UN evento → tomamos el snapshot correspondiente
        oldArr.forEach((oldSnap, idx) => {
    
            if (!oldSnap.control) return;
    
            const diffs = getControlDiffs(
                oldSnap.control,
                newObj.control
            );
    
            html.push(`
                <div class="history-snapshot mb-3">
                    <div class="snapshot-title fw-bold mb-1">
                        Antes / Después
                    </div>
    
                    ${diffs.length
                        ? renderDiffTable(diffs)
                        : `<em>Sin cambios visibles</em>`
                    }
                </div>
            `);
        });
    
        return html.join('');
    }
    
    function getControlDiffs(oldCtrl, newCtrl) {
        const campos = Object.keys(newCtrl);
        const diffs = [];
    
        campos.forEach(campo => {
            if (
                oldCtrl[campo] !== undefined &&
                oldCtrl[campo] != newCtrl[campo]
            ) {
                diffs.push({
                    campo,
                    old: oldCtrl[campo],
                    new: newCtrl[campo]
                });
            }
        });
    
        return diffs;
    }
    function formatValue(val) {
        if (val === null || val === undefined || val === '') return '—';
        if (typeof val === 'object') return JSON.stringify(val);
        return String(val);
    }
    
    function renderDiffTable(diffs) {
        return `
            <table class="table table-sm table-bordered mt-2 diff-table">
                <thead>
                    <tr>
                        <th>Campo</th>
                        <th>Antes</th>
                        <th>Ahora</th>
                    </tr>
                </thead>
                <tbody>
                    ${diffs.map(d => `
                        <tr>
                            <td><strong>${d.campo}</strong></td>
                            <td class="text-danger">
                                ${formatValue(d.old)}
                            </td>
                            <td class="text-success fw-bold">
                                ${formatValue(d.new)}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }
    

    function renderBookingDetailsDiff(oldDetails = [], newDetails = []) {
        return `
            <div class="row mt-2">
                <div class="col-md-6">
                    <small class="text-muted">Antes</small>
                    <pre class="bg-light p-2">${JSON.stringify(oldDetails, null, 2)}</pre>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Después</small>
                    <pre class="bg-light p-2">${JSON.stringify(newDetails, null, 2)}</pre>
                </div>
            </div>
        `;
    }
    function normalizeOldData(oldData) {
        const arr = safeJSON(oldData, []);
        if (!Array.isArray(arr)) return [];
    
        return arr.map(snap => {
            if (typeof snap === 'string') {
                return safeJSON(snap, {});
            }
            return snap;
        });
    }
    
    // =====================
    // UTILS
    // =====================
    function safeJSON(val, fallback) {
        try { return JSON.parse(val); }
        catch { return fallback; }
    }

    function debounce(fn, delay) {
        let t;
        return function () {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), delay);
        };
    }

});
