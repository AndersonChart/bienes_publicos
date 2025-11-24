<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters">
    </div>
    <div class="basics-container">
        <div class="new_user new_user-usuario" data-modal-target="new_area">+ Nuevo</div>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstado" class="btn_toggle-estado estado-rojo">Deshabilitadas</div>
        <?php endif; ?>
    </div>
</div>

<!-- Ventana Modal -->
<!-- Formulario registro/actualización -->
<dialog data-modal="new_area" class="modal modal_new-area">
    <!-- Botón de cerrar modal -->
    <button class="modal__close" type="button">X</button>

    <h2 class="modal_title">Registro de área</h2>
    <form id="form_nueva_area" method="POST" autocomplete="off" class="user_container">
        
        <input type="hidden" name="area_id" id="area_id">
        
        <div class="input_block_content">
            <label for="area_codigo" class="input_label">Código*</label>
            <input type="text" maxlength="20" name="area_codigo" class="input_text" id="area_codigo" autofocus>
        </div>

        <div class="input_block_content">
            <label for="area_nombre" class="input_label">Nombre*</label>
            <input type="text" id="area_nombre" name="area_nombre" class="input_text">
        </div>

        <div class="input_block_content">
            <label for="area_descripcion" class="input_label">Descripción</label>
            <input type="text" id="area_descripcion" name="area_descripcion" class="input_text">
        </div>

        <div id="error-container-area" class="error-container"></div>
        <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_area">
    </form>
</dialog>

<!-- Información de registro -->
<dialog data-modal="info_area" class="modal modal_info">
    <div class="modal_header-info">
        <form method="dialog">
            <button class="modal__close">X</button>
        </form>
        <h2 class="modal_title modal_title-info">Información del área</h2>
    </div>

    <!-- Contenedor de datos con scroll si excede -->
    <div class="info_container">
        <ul class="info_lista">
            <li><strong class="info_subtitle">Código:</strong> <span class="info_data" id="info_codigo_area"></span></li>
            <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_nombre_area"></span></li>
            <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_descripcion_area"></span></li>
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

<!-- Confirmación de eliminar -->
<dialog data-modal="eliminar_area" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de deshabilitar esta área? <br>podría ocasionar problemas</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="delete_codigo_area"></span>
        <span class="delete_data" id="delete_nombre_area"></span>
        <span class="delete_data" id="delete_descripcion_area"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_delete_area" method="POST">
            <input type="submit" value="Aceptar" name="delete" class="register_submit-confirm" id="btn_borrar_area">
        </form>
    </div>
</dialog>

<!-- Confirmación de recuperar -->
<dialog data-modal="confirmar_area" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de recuperar esta área?</h2>
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="confirmar_codigo_area"></span>
        <span class="delete_data" id="confirmar_nombre_area"></span>
        <span class="delete_data" id="confirmar_descripcion_area"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_confirmar_area" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_confirmar_area">
        </form>
    </div>
</dialog>

<div class="container_table_box">
    <div class="top"></div> <!-- Aquí se insertan los botones y búsqueda -->

    <table id="areaTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="4" class="title">Áreas</th>
            </tr>
            <tr>
                <th class="header">Código</th>
                <th class="header">Nombre</th>
                <th class="header">Descripción</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    
    <div class="bottom paginador"></div>
</div>

<script src="js/area_datatable.js"></script>
