<!-- Filtros y botones superiores -->
<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters">
        <label for="categoria" class="input_label">Categoría:</label>
        <select name="categoria_id" id="categoria_filtro" class="input_text input_select categoria_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>
        <label for="clasificacion" class="input_label">Clasificación:</label>
        <select name="clasificacion_id" id="clasificacion_filtro" class="input_text input_select clasificacion_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>
    </div>
    <div class="basics-container">
        <div class="new_user new_user-usuario" data-modal-target="new_articulo">+ Nuevo</div>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstado" class="btn_toggle-estado estado-rojo">Deshabilitados</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Registro/actualización -->
<dialog data-modal="new_articulo" class="modal modal_new-articulo">
    <form method="dialog"><button class="modal__close">X</button></form>
    <h2 class="modal_title">Registro de Artículo</h2>
    <form id="form_nuevo_articulo" method="POST" enctype="multipart/form-data" autocomplete="off" class="user_container">
        <input type="hidden" name="articulo_id" id="articulo_id">

        <div class="input_block_content">
            <label for="articulo_codigo" class="input_label">Código*</label>
            <input type="text" maxlength="20" name="articulo_codigo" class="input_text" id="articulo_codigo" autofocus>
        </div>

        <div class="input_block_content">
            <label for="articulo_nombre" class="input_label">Nombre*</label>
            <input type="text" id="articulo_nombre" name="articulo_nombre" class="input_text">
        </div>

        <!-- Categoría: filtro visual, sin name -->
        <div class="input_block_content">
            <label for="categoria_form-articulo" class="input_label">Categoría*</label>
            <select id="categoria_form-articulo" class="input_text input_select categoria_form">
                <option value="" selected disabled>Seleccione una categoría</option>
            </select>
        </div>

        <!-- Clasificación: obligatorio, se envía al backend -->
        <div class="input_block_content">
            <label for="clasificacion_form-articulo" class="input_label">Clasificación*</label>
            <select name="clasificacion_id" id="clasificacion_form-articulo" class="input_text input_select clasificacion_form">
                <option value="" selected disabled>Seleccione una clasificación</option>
            </select>
        </div>

        <div class="input_block_content campo-completo" id="bloque_marca">
            <label for="marca_form-articulo" class="input_label">Marca*</label>
            <select name="marca_id" id="marca_form-articulo" class="input_text input_select marca_form">
                <option value="" selected disabled>Seleccione una marca</option>
            </select>
        </div>

        <div class="input_block_content campo-completo" id="bloque_modelo">
            <label for="articulo_modelo" class="input_label">Modelo*</label>
            <input type="text" id="articulo_modelo" name="articulo_modelo" class="input_text">
        </div>

        <div class="input_block_content">
            <label for="articulo_descripcion" class="input_label">Descripción</label>
            <input type="text" id="articulo_descripcion" name="articulo_descripcion" class="input_text">
        </div>

        <div class="input_block_content">
            <label for="foto_articulo" class="input_label">Imagen</label>
            <div class="foto_perfil_container">
                <input type="file" id="foto_articulo" name="articulo_imagen" class="input_file" accept=".jpg,.jpeg,.png">
                <div class="foto_imagen_wrapper" onclick="document.getElementById('foto_articulo').click()">
                    <img id="preview_foto_articulo" class="foto_imagen" alt="Foto del artículo">
                    <span class="foto_perfil_icon imagen_icon"></span>
                </div>
            </div>
        </div>

        <div id="error-container-articulo" class="error-container"></div>
        <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_articulo">
    </form>
</dialog>

<!-- Modal: Información -->
<dialog data-modal="info_articulo" class="modal modal_info">
    <div class="modal_header-info">
        <form method="dialog"><button class="modal__close">X</button></form>
        <h2 class="modal_title modal_title-info">Información del artículo</h2>
    </div>
    <div class="img_info">
        <img id="info_imagen" class="foto_info imagen_info">
    </div>
    <div class="info_container">
        <ul class="info_lista">
            <li><strong class="info_subtitle">Código:</strong> <span class="info_data" id="info_codigo"></span></li>
            <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_nombre"></span></li>
            <li><strong class="info_subtitle">Categoría:</strong> <span class="info_data" id="info_categoria"></span></li>
            <li><strong class="info_subtitle">Clasificación:</strong> <span class="info_data" id="info_clasificacion"></span></li>
            <li id="li_info_marca">
                <strong class="info_subtitle">Marca:</strong> 
                <span class="info_data" id="info_marca"></span>
            </li>
            <li id="li_info_modelo">
                <strong class="info_subtitle">Modelo:</strong> 
                <span class="info_data" id="info_modelo"></span>
            </li>
            <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_descripcion"></span></li>
        </ul>
    </div>
</dialog>

<!-- Modal: Éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Proceso éxitoso!</h2>
        <p class="modal_success-message" id="success-message"></p>
        <button class="modal__close-success" id="close-success_articulo">Aceptar</button>
    </form>
</dialog>

<!-- Modal: Confirmar eliminación -->
<dialog data-modal="eliminar_articulo" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de deshabilitar este artículo? <br>podría ocasionar problemas</h2>
    </div>
    <div class="img_info">
        <img id="delete_imagen_articulo" class="foto_info imagen_info">
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="delete_codigo_articulo"></span>
        <span class="delete_data" id="delete_nombre_articulo"></span>
        <span class="delete_data" id="delete_categoria_articulo"></span>
        <span class="delete_data" id="delete_clasificacion_articulo"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog"><button class="modal__close modal__close-confirm">Cancelar</button></form>
        <form id="form_delete_articulo" method="POST">
            <input type="submit" value="Aceptar" name="delete" class="register_submit-confirm" id="btn_borrar">
        </form>
    </div>
</dialog>

<!-- Modal: Confirmar recuperación -->
<dialog data-modal="confirmar_articulo" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de recuperar este artículo?</h2>
    </div>
    <div class="img_info">
        <img id="confirmar_imagen_articulo" class="foto_info imagen_info">
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="confirmar_codigo_articulo"></span>
        <span class="delete_data" id="confirmar_nombre_articulo"></span>
        <span class="delete_data" id="confirmar_categoria_articulo"></span>
        <span class="delete_data" id="confirmar_clasificacion_articulo"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog"><button class="modal__close modal__close-confirm">Cancelar</button></form>
        <form id="form_confirmar_articulo" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_confirmar">
        </form>
    </div>
</dialog>

<!-- Tabla -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="articuloTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="8" class="title">Artículos</th>
            </tr>
            <tr>
                <th class="header">Código</th>
                <th class="header">Nombre</th>
                <th class="header">Categoría</th>
                <th class="header">Clasificación</th>
                <th class="header">Modelo</th>
                <th class="header">Marca</th>
                <th class="header">Imagen</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="bottom paginador"></div>
</div>

<script src="js/articulo_datatable.js"></script>
