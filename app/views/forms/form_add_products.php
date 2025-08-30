<section style="padding: 4px;">
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Tipo de transporte -->
        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Tipo de Transporte:</label> <span style="color: red;">*</span>
                <div>
                    <label><input type="radio" name="transport_type" value="terrestre" checked> Terrestre</label>
                    <label style="margin-left: 20px;"><input type="radio" name="transport_type" value="maritimo"> Marítimo</label>
                </div>
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Tipo de traslado:</label> <span style="color: red;">*</span>
                <div>
                    <label><input type="radio" name="trip_type" value="sencillo" checked> Sencillo</label>
                    <label style="margin-left: 20px;"><input type="radio" name="trip_type" value="redondo"> Redondo</label>
                </div>
            </div>
        </div>

        <!-- Nombre, Fecha y Personas -->
        <div style="display: flex; flex-direction: row; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Nombre de cliente:</label>
                <input type="text" name="transport_client_name" class="form-control ds-input" placeholder="Nombre completo">
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Fecha:</label>
                <input type="date" name="transport_date" class="form-control ds-input">
            </div>
            <div class="form-group" style="flex: 0.5;">
                <label style="font-weight: 700;">Personas:</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn-icon" id="decreasePerson"><i class="material-icons">remove</i></button>
                    <span id="numPersons">2</span>
                    <button type="button" class="btn-icon" id="increasePerson"><i class="material-icons">add</i></button>
                </div>
            </div>
        </div>

        <!-- Transporte -->
        <div>
            <div style="font-weight: bold; color: #03a9f4;">Transporte de Ida →</div>
        </div>

        <!-- Lugar y hora -->
        <div style="display: flex; flex-direction: row; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Punto de partida:</label>
                <input type="text" name="pickup_point" class="form-control ds-input" placeholder="Ej: Hotel Westin...">
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Destino:</label>
                <input type="text" name="destination_point" class="form-control ds-input" placeholder="Ej: Marina Punta Norte">
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: 700;">Horario:</label>
                <input type="time" name="departure_time" class="form-control ds-input">
            </div>
        </div>

        <!-- Comentarios -->
        <div class="form-group">
            <label style="font-weight: 700;">Comentario:</label>
            <textarea name="transport_comment" class="form-control ds-input" rows="2" placeholder="Agrega detalles adicionales..."></textarea>
        </div>

        <!-- Acciones -->
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn btn-primary" id="confirmTransport"><i class="material-icons left">check</i>CONFIRMO</button>
            <button class="btn btn-light" onclick="cancelTransportModal();"><i class="material-icons left">close</i>CANCELAR</button>
        </div>
    </div>
</section>
<script>
        $(document).ready(function () {
        let numPersons = 2;

        $('#increasePerson').click(function () {
            numPersons++;
            $('#numPersons').text(numPersons);
        });

        $('#decreasePerson').click(function () {
            if (numPersons > 1) {
                numPersons--;
                $('#numPersons').text(numPersons);
            }
        });

        $('#confirmTransport').click(function () {
            // Aquí puedes extraer los datos y hacer submit o AJAX
            const tipoTransporte = $('input[name="transport_type"]:checked').val();
            const tipoTraslado = $('input[name="trip_type"]:checked').val();
            const cliente = $('input[name="transport_client_name"]').val();
            const fecha = $('input[name="transport_date"]').val();
            const salida = $('input[name="pickup_point"]').val();
            const destino = $('input[name="destination_point"]').val();
            const hora = $('input[name="departure_time"]').val();
            const comentario = $('textarea[name="transport_comment"]').val();

            console.log({
                tipoTransporte,
                tipoTraslado,
                cliente,
                fecha,
                numPersons,
                salida,
                destino,
                hora,
                comentario
            });

            // Aquí iría tu lógica para guardar datos o cerrar modal
        });
    });

    function cancelTransportModal() {
        // Cierra el modal y/o reinicia los campos
        if (typeof widgetModal !== "undefined") widgetModal.close();
        else $('#modalTransporte').fadeOut();
    }

</script>