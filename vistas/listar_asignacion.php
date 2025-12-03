<div class="banner_list">
    <div class="filters">
        <label for="cargo_filtro" class="input_label">Cargo:</label>
        <select name="cargo_id" id="cargo_filtro" class="input_text input_select cargo_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>

        <label for="persona_filtro" class="input_label">Persona:</label>
        <select name="persona_id" id="persona_filtro" class="input_text input_select persona_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>

        <label for="area_filtro" class="input_label">Área:</label>
        <select name="area_id" id="area_filtro" class="input_text input_select area_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>
    </div>

    <div class="basics-container">
        <a href="index.php?vista=procesar_asignacion"><div class="new_user new_user-usuario">+ Nueva</div></a>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstadoAsignacion" class="btn_toggle-estado estado-rojo">Deshabilitados</div>
        <?php endif; ?>
    </div>
</div>


<!-- Modal: Info Asignacion -->
<dialog data-modal="info_asignacion" class="modal_new-recepcion">
    <div class="proceso_container">
        <div class="modal_header-info">
            <form method="dialog"><button class="modal__close">X</button></form>
            <h2 class="modal_title modal_title-info">Información de la asignación</h2>
        </div>

        <div class="info_container">
            <ul class="info_lista">
                <li><strong class="info_subtitle">ID:</strong> <span class="info_data" id="info_id"></span></li>
                <li><strong class="info_subtitle">Área:</strong> <span class="info_data" id="info_area"></span></li>
                <li><strong class="info_subtitle">Personal:</strong> <span class="info_data" id="info_persona"></span></li>
                <li><strong class="info_subtitle">Cargo:</strong> <span class="info_data" id="info_cargo"></span></li>
                <li><strong class="info_subtitle">Fecha de Inicio:</strong> <span class="info_data" id="info_fecha"></span></li>
                <li><strong class="info_subtitle">Fecha de Finalización:</strong> <span class="info_data" id="info_fecha_fin"></span></li>
                <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_descripcion"></span></li>
            </ul>
        </div>
        <div class="resumen_container">
            <div class="container_table_box-recepcion">
                <table id="asignacionResumenTabla" class="display">
                    <thead>
                        <tr>
                            <th colspan="4" class="title">Resumen de la Asignación</th>
                        </tr>
                        <tr>
                            <th class="header">Código</th>
                            <th class="header">Nombre</th>
                            <th class="header">Cantidad</th>
                            <th class="header">Seriales</th>
                        </tr>
                    </thead>
                </table>    
            </div>
        </div>
    </div>
</dialog>


<!-- Mensaje de éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Proceso éxitoso!</h2>
        <p class="modal_success-message" id="success-message"></p>
        <button class="modal__close-success" id="close-success-asignacion">Aceptar</button>
    </form>
</dialog>

<!-- Mensaje de error -->
<dialog data-modal="error" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon-error"></div>
        <h2 class="modal_title">¡Ocurrió un error!</h2>
        <p class="modal_error-message" id="error-message"></p>
        <button class="modal__close-success" id="close-error-asignacion">Aceptar</button>
    </form>
</dialog>

<!-- Confirmación de anular -->
<dialog data-modal="anular_asignacion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de anular esta asignación?</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="anular_asignacion_id"></span>
        <span class="delete_data" id="anular_asignacion_descripcion"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_anular_asignacion" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_anular_asignacion">
        </form>
    </div>
</dialog>

<!-- Confirmación de recuperar -->
<dialog data-modal="recuperar_asignacion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de recuperar esta asignación?</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="recuperar_asignacion_id"></span>
        <span class="delete_data" id="recuperar_asignacion_descripcion"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_recuperar_asignacion" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_recuperar_asignacion">
        </form>
    </div>
</dialog>


<!-- Tabla -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="asignacionTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="7" class="title">Asignaciones</th>
            </tr>
            <tr>
                <th class="header">ID</th>
                <th class="header">Personal</th>
                <th class="header">Cargo</th>
                <th class="header">Área</th>
                <th class="header">Desde:</th>
                <th class="header">Hasta:</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="bottom paginador"></div>
</div>

<script src="js/asignacion_datatable.js"></script>
