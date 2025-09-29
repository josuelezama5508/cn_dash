<div>
    <form id="form-add-products" style="display: flex; flex-direction: column; gap: 10px;">
        <div id="form-1" style="display: flex; flex-direction: column; gap: 10px;">
            <div class="form-group">
                <label for="select-company" id="select-company" style="font-weight: 700;">Empresa:</label> <span style="color: red;">*</span>
                <div style="display: flex; flex-direction: row; gap: 8px;">
                    <div style="width: 50px; height: 40px;">
                        <img id="logocompany" style="width: 100%; height: 100%; object-fit: contain; border: none;">
                    </div>
                    <div style="width: 100%;">
                        <select name="company" id="company" class="form-control ds-input"></select>
                    </div>
                </div>
            </div>
            <!--  -->
            <div class="form-group">
                <label for="product-code" style="font-weight: 700;">Codigo de producto:</label> <span style="color: red;">*</span>
                <input type="text" name="productcode" id="product-code" class="form-control ds-input input-productcode">
            </div>
        </div>
        <div id="form-2" class="table-container" style="max-height: 350px; overflow-y: auto; display: block;">
            <table class="table table-scrollbar" style="margin: 0; width: 100%; border-collapse: collapse;">
                <thead style="z-index: 4;">
                    <tr style="color: black; font-size:16px;">
                        <th scope="col" style="width: 82px;">Idioma</th>
                        <th scope="col">Nombre <span style="color: red;">*</span></th>
                        <th scope="col" style="width: 110px;">Precio</th>
                        <th scope="col" style="width: 130px;">Denominacion</th>
                        <th scope="col" style="width: 38px;">Estatus panel</th>
                        <th scope="col" style="width: 38px;">Estatus web</th>
                        <th scope="col" style="width: 38px;"></th>
                    </tr>
                </thead>
                <tbody id="RProducts" style="border-bottom: 1px solid #DDD;"></tbody>
            </table>
        </div>
    </form>

    <template id="tplRow">
        <tr style="border-top: 1px solid #DDD;">
            <td>
                <div class="form-group">
                    <select name="productlang[]" class="form-control ds-input"></select>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <input type="text" name="productname[]" class="form-control ds-input input-productname">
                </div>
            </td>
            <td>
                <div class="form-group">
                    <select name="productprice[]" class="form-control ds-input"></select>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <select name="denomination[]" class="form-control ds-input"></select>
                </div>
            </td>
            <td>
                <div style="display: flex; vertical-align: middle; align-items: center;">
                    <span class="input-checkbox" data-name="showpanel[]"></span>
                    <input type="hidden" name="showpanel[]" value="0">
                </div>
            </td>
            <td>
                <div style="display: flex; vertical-align: middle; align-items: center;">
                    <span class="input-checkbox" data-name="showweb[]"></span>
                    <input type="hidden" name="showweb[]" value="0">
                </div>
            </td>
            <td>
                <div style="display: flex; vertical-align: middle; align-items: center;">
                    <i class="small material-icons delete-item" style="color: red; cursor: pointer;">cancel</i>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <textarea name="description[]" class="form-control ds-input"></textarea>
            </td>
        </tr>
    </template>
</div>

<div class="form-group" style="margin-top: 20px;">
    <button id="addProductItem" class="btn-icon">
        <i class="material-icons left">add</i>ADD PRODUCT
    </button>
</div>