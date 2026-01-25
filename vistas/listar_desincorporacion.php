<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters"></div>
    <div class="basics-container">
        <a href="index.php?vista=procesar_desincorporacion"><div class="new_user new_user-usuario">+ Nueva</div></a>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstadodesincorporacion" class="btn_toggle-estado estado-rojo">Anuladas</div>
        <?php endif; ?>
    </div>
</div>

<!-- Información de Desincorporación -->    
<dialog data-modal="info_desincorporacion" class="modal_new-desincorporacion">
    <div class="proceso_container">
        <div class="modal_header-info">
            <form method="dialog">
                <button class="modal__close">X</button>
            </form>
            <h2 class="modal_title modal_title-info">Información de la Desincorporación</h2>
        </div>
        <div class="info_container">
            <ul class="info_lista">
                <li><strong class="info_subtitle">ID:</strong> <span class="info_data" id="info_desincorporacion_id"></span></li>
                <li><strong class="info_subtitle">Fecha:</strong> <span class="info_data" id="info_desincorporacion_fecha"></span></li>
                <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_desincorporacion_descripcion"></span></li>
            </ul>
        </div>
        <div class="resumen_container">
            <div class="container_table_box-desincorporacion">
                <table id="desincorporacionResumenTabla" class="display">
                    <thead>
                        <tr>
                            <th colspan="4" class="title">Resumen de la Desincorporación</th>
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
        <button class="modal__close-success" id="close-success">Aceptar</button>
    </form>
</dialog>

<!-- Mensaje de error -->
<dialog data-modal="error" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon-error"></div>
        <h2 class="modal_title">¡Ocurrió un error!</h2>
        <p class="modal_error-message" id="error-message"></p>
        <button class="modal__close-success" id="close-error">Aceptar</button>
    </form>
</dialog>

<!-- Confirmación de anular -->
<dialog data-modal="anular_desincorporacion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de anular esta Desincorporación?</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="anular_desincorporacion_id"></span>
        <span class="delete_data" id="anular_desincorporacion_descripcion"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_anular_desincorporacion" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_anular_desincorporacion">
        </form>
    </div>
</dialog>

<!-- Confirmación de recuperar -->
<dialog data-modal="recuperar_desincorporacion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de recuperar esta Desincorporación?</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="recuperar_desincorporacion_id"></span>
        <span class="delete_data" id="recuperar_desincorporacion_descripcion"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_recuperar_desincorporacion" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_recuperar_desincorporacion">
        </form>
    </div>
</dialog>


<!-- Tabla de desincorporaciones -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="recepcionTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="4" class="title">Desincorporaciones</th>
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
