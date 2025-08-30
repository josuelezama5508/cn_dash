<section style="padding: 4px;">
  <div style="display: flex; flex-direction: column; gap: 20px; max-width: 600px;">

    <input type="hidden" name="combo_id" value="">

    <form id="form-combo" style="display: flex; flex-direction: column; gap: 20px;">

    <div class="form-group">
      <label style="font-weight: 700;">Código del producto:</label>
      <div id="product-code-label" style="margin-top: 6px; font-weight: bold;"></div>
    </div>


      <!-- Productos del combo (checkbox table) -->
      <div class="form-group">
        <label style="font-weight: 700;">Productos en combo:</label>
        <div id="combo-products-table-container" style="margin-top: 6px;">
          <!-- Tabla generada dinámicamente con checkboxes -->
        </div>
      </div>

      <!-- Estado -->
      <div class="form-group">
        <label style="font-weight: 700;">Estado:</label>
        <select name="status" class="form-control" style="margin-top: 6px;">
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
      </div>
    </form>

  </div>
</section>
