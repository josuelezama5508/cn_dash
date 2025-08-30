function edit_item_product(element) {
    if (modal_product && modal_product.isOpen) {
        modal_product.close();
        modal_product = null;
    }
    let companyid = $("#company").val() || globalCompanyId;
    let condition = $("[name='productcode']").val();
    let id = $(element).attr("id");

    modal_product = $.confirm({
        title: `Editar producto: <span style="color: royalblue;">${condition}</span>`,
        content: `url:${window.url_web}/form/edit_product?id=${id}`,
        boxWidth: "980px",
        useBootstrap: false,
        buttons: {
            ok: {
                text: "Aceptar",
                btnClass: "btn-green",
                action: () => {
                    if (typeof sendEvent === 'function') sendEvent(modal_product);
                    return false;
                }
            },
            no: {
                text: "Cancelar",
                btnClass: "btn-red",
                action: () => {
                    if (typeof cancelEvent === "function") cancelEvent(modal_product);
                    return false;
                }
            }
        }
    });
}
