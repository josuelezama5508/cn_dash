// --- API: Combos ---

async function fetch_combo_by_code(productcode) {
    try {
        const response = await fetchAPI(`combo?getComboCode=${productcode}`, 'GET');
        const result = await response.json();
        if (response.status === 200 && result.data) {
            return result.data[0];
        }
        return null;
    } catch (err) {
        console.error("Error obteniendo combo por código:", err);
        return null;
    }
}

async function fetch_products_combo(productcode) {
    try {
        const response = await fetchAPI(`combo?getProductsCombo=${productcode}`, 'GET');
        const result = await response.json();
        if (response.status === 200 && result.data) {
            return result.data;
        }
        return {};
    } catch (err) {
        console.error("Error obteniendo productos del combo:", err);
        return {};
    }
}

async function update_combo(idCombo, combosSeleccionados) {
    const payload = {
        combosUp: {
            id: idCombo,
            combos: JSON.stringify(combosSeleccionados)
        }
    };

    try {
        const response = await fetchAPI("combo", "PUT", payload);
        const result = await response.json();

        if (response.status === 200) {
            location.reload();
        } else {
            alert("Error al actualizar combo: " + (result.message || ""));
        }
    } catch (err) {
        console.error("Error actualizando combo:", err);
    }
}

async function save_combo(formData) {
    const combo_id = formData.get("combo_id");
    const endpoint = combo_id ? `comboproducts?id=${combo_id}` : `comboproducts`;
    const method = combo_id ? "PUT" : "POST";

    try {
        const response = await fetchAPI(endpoint, method, formData);
        const result = await response.json();

        if (response.status === 200 || response.status === 201) {
            alert("Combo guardado correctamente");
            if (modal_combo && modal_combo.isOpen) modal_combo.close();
            else location.reload();
        } else {
            alert("Error al guardar el combo");
        }
    } catch (err) {
        console.error("Error guardando combo:", err);
    }
}
// --- API: Combos (Wrapper equivalente a registered_combos) ---
async function registered_combos(productcode) {
    try {
        const comboData = await fetch_combo_by_code(productcode);
        if (comboData) {
            idCombo = comboData.id;
        }

        const productsCombo = await fetch_products_combo(productcode);
        if (productsCombo && Object.keys(productsCombo).length > 0) {
            globalRegisteredCombos = productsCombo;
            render_combo_products(productsCombo); // 👈 ojo, este ya está en combosrender.js
        } else {
            console.warn("No se encontraron productos en el combo.");
        }
    } catch (err) {
        console.error("Error en registered_combos:", err);
    }
}

