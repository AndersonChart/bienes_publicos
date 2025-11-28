<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters"></div>
    <div class="basics-container">
        <div class="new_user new_user-usuario" data-modal-target="new_recepcion">+ Nueva Recepción</div>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstadoRecepcion" class="btn_toggle-estado estado-rojo">Anuladas</div>
        <?php endif; ?>
    </div>
</div>

<!-- Ventana Modal -->
<!-- Formulario registro/actualización -->
<dialog data-modal="new_recepcion" class="modal modal_new-recepcion">
    <button class="modal__close" type="button">X</button>
    <h2 class="modal_title">Registro de Recepción</h2>
    <form id="form_nueva_recepcion" method="POST" autocomplete="off" class="user_container">
        <input type="hidden" name="recepcion_id" id="recepcion_id">

        <div class="input_block_content">
            <label for="recepcion_fecha" class="input_label">Fecha*</label>
            <input type="date" id="recepcion_fecha" name="recepcion_fecha" class="input_text">
        </div>

        <div class="input_block_content">
            <label for="recepcion_descripcion" class="input_label">Descripción*</label>
            <input type="text" id="recepcion_descripcion" name="recepcion_descripcion" class="input_text">
        </div>

        <div id="error-container-recepcion" class="error-container"></div>
        <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_recepcion">
    </form>
</dialog>

<!-- Información de recepción -->
<dialog data-modal="info_recepcion" class="modal modal_info">
    <div class="modal_header-info">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>
        <h2 class="modal_title modal_title-info">Información de la Recepción</h2>
    </div>
    <div class="info_container">
        <ul class="info_lista">
            <li><strong class="info_subtitle">ID:</strong> <span class="info_data" id="info_recepcion_id"></span></li>
            <li><strong class="info_subtitle">Fecha:</strong> <span class="info_data" id="info_recepcion_fecha"></span></li>
            <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_recepcion_descripcion"></span></li>
        </ul>
    </div>
</dialog>

<!-- Mensaje de éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Proceso éxitoso!</h2>
        <p class="modal_success-message" id="success-message"></p>
        <button class="modal__close-success" id="close-success">Aceptar</button>
    </form>
</dialog>

<!-- Confirmación de anular -->
<dialog data-modal="anular_recepcion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de anular esta recepción?</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="anular_recepcion_id"></span>
        <span class="delete_data" id="anular_recepcion_descripcion"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_anular_recepcion" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_anular_recepcion">
        </form>
    </div>
</dialog>

<!-- Tabla de recepciones -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="recepcionTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="4" class="title">Recepciones</th>
            </tr>
            <tr>
                <th class="header">ID</th>
                <th class="header">Fecha</th>
                <th class="header">Descripción</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="bottom paginador"></div>
</div>

<script src="js/recepcion_datatable.js"></script>
